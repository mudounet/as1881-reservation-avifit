<?php
// SQLite database file path
require_once 'includes/globals.php';

$database = False;
try {
	// Create or open the database
	$databaseWillBeCreated = !file_exists(SQLITE_DB_PATH);
	$database = connectToDatabase();
	if ($databaseWillBeCreated) {
		createTables($database);
		
		foreach (CATEGORIES as $cat_key => $description) {
			createCategory($database, $cat_key, $description);
		}
		getUser($database, "AUTO_EVENT", "a00oisxf5pgi7wx16tpcsgf35ilmm335e0@miauou.net");
		echo "Database initialised. Please reload page...";
		exit;
	}
} catch (PDOException $e) {
	echo "Error while creating database: " . $e->getMessage();
	exit;
}

// Create the database connection
function connectToDatabase() {
	try {
		$database = new PDO('sqlite:' . SQLITE_DB_PATH);
		$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $database;
	} catch (PDOException $e) {
		echo "Error connectToDatabase(): " . $e->getMessage();
		return null;
	}
}

// Function to create the "users" table if it doesn't exist
function createTables(PDO $database) {
	try {
		$database->exec("CREATE TABLE IF NOT EXISTS schema_version (version INTEGER)");
		$database->exec("INSERT INTO schema_version (version) VALUES (1)");
		
		$createCategoriesTable = 'CREATE TABLE IF NOT EXISTS "categories" (
			"id"	INTEGER,
			"textual_id" TEXT NOT NULL UNIQUE,
			"description"	TEXT,
			PRIMARY KEY("id" AUTOINCREMENT)
			)';
		$database->exec($createCategoriesTable);
		
		$createEventTable = 'CREATE TABLE IF NOT EXISTS "events" (
			"id"	INTEGER,
			"ts_sxb_start"	INTEGER NOT NULL,
			"ts_sxb_end"	INTEGER NOT NULL,
			"title"	TEXT,
			"description"	TEXT,
			"category_id"	INTEGER NOT NULL,
			"referee"	TEXT,
			"category_auto"	TEXT,
			"places_min"	INTEGER,
			"places_max"	INTEGER,
			"disactivation_text"	TEXT,
			"submitter_id"	INTEGER,
			PRIMARY KEY("id" AUTOINCREMENT),
			FOREIGN KEY("category_id") REFERENCES categories(id),
			FOREIGN KEY("submitter_id") REFERENCES users(id)
			)';
		$database->exec($createEventTable);
		
		$createUserTable = 'CREATE TABLE IF NOT EXISTS "users" (
			"id"	INTEGER,
			"name"	TEXT,
			"display_name"	TEXT NOT NULL,
			"email"	TEXT NOT NULL UNIQUE,
			"is_admin" INTEGER,
			"is_avifit" INTEGER,
			"last_access" INTEGER,
			PRIMARY KEY("id" AUTOINCREMENT)
			)';
		$database->exec($createUserTable);
		
		$createSubscriptionTable = 'CREATE TABLE IF NOT EXISTS "subscriptions" (
			"id"	INTEGER,
			"event_id"	INTEGER NOT NULL,
			"user_id"	INTEGER NOT NULL,
			"timestamp"	INTEGER,
			"is_waiting_list"	INTEGER,
			PRIMARY KEY("id" AUTOINCREMENT),
			FOREIGN KEY("event_id") REFERENCES events(id),
			FOREIGN KEY("user_id") REFERENCES users(id)
			)';
		$database->exec($createSubscriptionTable);
	} catch (PDOException $e) {
		echo "Error createTables(): " . $e->getMessage();
	}
	
	require_once('manual_db_upgrade.php');
	performDbUpgrade();
}

// Create a category
function createCategory(PDO $database, $textual_id, $description) {
	try {
		$query = 'INSERT INTO categories (textual_id, description) VALUES (?, ?)';
		$statement = $database->prepare($query);
		$statement->execute([$textual_id, $description]);
		return $database->lastInsertId();
	} catch (PDOException $e) {
		echo "Error createCategory(): " . $e->getMessage();
		return false;
	}
}

// Retrieve all users
function getCategories(PDO $database) {
	return _getWholeTable($database, 'categories');
}

// Retrieve all used categories
function getUsedCategories(PDO $database, int $timestamp) {
	return _getWithQuery($database, 'SELECT DISTINCT c.* FROM categories c JOIN events e ON c.id = e.category_id AND e.ts_sxb_end >= '.$timestamp.' ORDER BY c.id', False);
}

// Retrieve all categories
function getAllCategories(PDO $database, int $timestamp) {
	return _getWithQuery($database, 'SELECT DISTINCT * FROM categories ORDER BY id', False);
}

function createEvent(PDO $database, $ts_start, $ts_end, $title, $description, string $category_text, $referee, $auto_id, $places_min, $places_max, $desactivation_txt, $submitter_id, bool $tense_activity) {
	try {
		
		$query = 'INSERT INTO events ("ts_sxb_start", "ts_sxb_end", "title", "description", "category_id", "referee", "category_auto", "places_min", "places_max", "disactivation_text", "submitter_id", "tense_activity") VALUES (:ts_start, :ts_end, :title, :description, (SELECT id from categories WHERE textual_id = :category_text LIMIT 1), :referee, :cat_auto, :places_min, :places_max, :desactivation_txt, :submitter_id, :tense_activity);';
		$statement = $database->prepare($query);
		
		$statement->bindValue('ts_start', $ts_start, PDO::PARAM_INT);
		$statement->bindValue('ts_end', $ts_end, PDO::PARAM_INT);
		$statement->bindValue('title', $title, PDO::PARAM_STR);
		$statement->bindValue('description', $description, PDO::PARAM_STR);
		$statement->bindValue('category_text', $category_text, PDO::PARAM_STR);
		$statement->bindValue('referee', $referee, PDO::PARAM_STR);
		$statement->bindValue('cat_auto', $auto_id, PDO::PARAM_STR);
		$statement->bindValue('places_min', $places_min, PDO::PARAM_INT);
		$statement->bindValue('places_max', $places_max, PDO::PARAM_INT);
		$statement->bindValue('desactivation_txt', $desactivation_txt, PDO::PARAM_STR);
		$statement->bindValue('submitter_id', $submitter_id, PDO::PARAM_INT);
		$statement->bindValue('tense_activity', $tense_activity, PDO::PARAM_BOOL);
		
		$statement->execute();
		return $database->lastInsertId();
	} catch (PDOException $e) {
		echo "Error createEvent(): " . $e->getMessage();
		print $category_text;
		return false;
	}
}

function getEvent(PDO $database, $id) {
	return _getWithQuery($database, 'SELECT * FROM events WHERE id='.$id.' LIMIT 1', True);
}

function getCategoryId(PDO $database, $category_name) {
	$query = 'SELECT id from categories WHERE textual_id = :category_text LIMIT 1)';
	$statement = $database->prepare($query);
	$statement->bindValue('category_text', $category_text, PDO::PARAM_STR);
	$statement->execute();
	return $statement->fetch(PDO::FETCH_ASSOC)["id"];
}

function nullify($str) {
	return $str === '' ? null : $str; 
}

function updateEvent(PDO $database, $event_id, $ts_start, $ts_end, $title, $description, $category_id, $referee, $places_min, $places_max, $disactivation_txt) {
	try {
		$query = 'UPDATE events SET	ts_sxb_start = :ts_start, ts_sxb_end = :ts_end, title = :title, description = :description, category_id = :category_id, referee = :referee, places_min = :places_min, places_max = :places_max, disactivation_text = :disactivation_txt WHERE id = :event_id';
		$statement = $database->prepare($query);
		
		$statement->bindValue('event_id', $event_id, PDO::PARAM_INT);
		$statement->bindValue('ts_start', $ts_start, PDO::PARAM_INT);
		$statement->bindValue('ts_end', $ts_end, PDO::PARAM_INT);
		$statement->bindValue('title', nullify($title), PDO::PARAM_STR);
		$statement->bindValue('description', nullify($description), PDO::PARAM_STR);
		$statement->bindValue('category_id', nullify($category_id), PDO::PARAM_INT);
		$statement->bindValue('referee', nullify($referee), PDO::PARAM_STR);
		$statement->bindValue('places_min', nullify($places_min), PDO::PARAM_INT);
		$statement->bindValue('places_max', nullify($places_max), PDO::PARAM_INT);
		$statement->bindValue('disactivation_txt', nullify($disactivation_txt), PDO::PARAM_STR);
		
		$statement->execute();
		return true;
	} catch (PDOException $e) {
		echo "Error updateEvent(): " . $e->getMessage();
		echo "Query: ".$statement->queryString;
		return false;
	}
}

function isUserAlreadyRegistered(PDO $database, $event_id, $user_id) {
	return _getWithQuery($database, "SELECT COUNT(*) as c FROM subscriptions WHERE event_id = '$event_id' and user_id = '$user_id'", true)['c'] >= 1;
}

function get_users_in_event(PDO $database, int $event_id) {
	return _getWithQuery($database, "SELECT s.* FROM subscriptions s WHERE event_id='$event_id'", false);
}

function check_availability_event(PDO $database, int $event_id) {
	return _getWithQuery($database, "SELECT (SELECT COUNT(*) FROM subscriptions WHERE event_id = $event_id) < (SELECT places_max FROM events WHERE id = $event_id) as result", true)['result'];
}

function remove_user_in_event(PDO $database, int $event_id, int $user_id) {
	return _exec_query($database, "DELETE FROM subscriptions WHERE event_id = '$event_id' AND user_id='$user_id'");
}

function update_last_access_user(PDO $database, int $user_id) {
	return _exec_query($database, "UPDATE users SET last_access=CURRENT_TIMESTAMP WHERE id='$user_id'");
}

// Retrieve whole table
function _getWholeTable(PDO $database, $table) {
	return _getWithQuery($database, 'SELECT * FROM '.$table, False);
}

function _getWithQuery(PDO $database, $query, $oneResult) {
	try {
		$statement = $database->query($query);

		if ($oneResult) {
			return $statement->fetch(PDO::FETCH_ASSOC);
		} else {
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);
			if (is_array($result)) return $result;
			return [];
		}
	} catch (PDOException $e) {
		echo "Error _getWithQuery(): " . $e->getMessage();
		return [];
	}
}

function _exec_query(PDO $database, $query) {
	try {
		return $database->exec($query);
	} catch (PDOException $e) {
		echo "Error _exec_query(): " . $e->getMessage();
		return false;
	}
}


// Create or update a new user
function getUser(PDO $dbh, $display_name, $email) {
	try {
		// Check if the user already exists in the database
		$stmt = $dbh->prepare('SELECT * FROM users WHERE email=:email');
		$stmt->bindValue('email', $email, PDO::PARAM_STR);
		$stmt->execute();
		$userData = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($userData) {
			// User exists, check if display_name needs to be updated
			if ($userData['display_name'] == $display_name) return $userData;
			// Update display_name
			$stmt = $dbh->prepare('UPDATE users SET display_name = :display_name WHERE email = :email');
			$stmt->bindValue('display_name', $display_name, PDO::PARAM_STR);
			$stmt->bindValue('email', $email, PDO::PARAM_STR);
			$stmt->execute();

			$userData['display_name'] = $display_name; // Update the display_name in the userData array
		} else {
			// User does not exist, create a new user
			$stmt = $dbh->prepare('INSERT INTO users (display_name, email) VALUES (:display_name, :email)');
			$stmt->bindValue('display_name', $display_name, PDO::PARAM_STR);
			$stmt->bindValue('email', $email, PDO::PARAM_STR);
			$stmt->execute();

			$userData = [
				'id' => $dbh->lastInsertId(),
				'display_name' => $display_name,
				'email' => $email
			];
		}

		return $userData;
	} catch (PDOException $e) {
		echo "Error getUser(): " . $e->getMessage();
		return [];
	}
}

?>