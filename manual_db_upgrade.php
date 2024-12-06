<?php

require_once 'includes/database.php';



function performDbUpgrade() {
	$pdo = connectToDatabase();
	$currentVersion = null;
	try {
		// Get the current database version
		$stmt = $pdo->query("SELECT version FROM schema_version LIMIT 1");
		$currentVersion = $stmt->fetchColumn();
	} catch (PDOException $e) {	
		throw "error : $e";
	}

	// Check and update the database version
	try {
		echo "database is at version $currentVersion";
		$expectedVersion = 3;
		if ($currentVersion === false) {
			// No version found, initialize the database version
			throw "error";
		}
		
		if ($currentVersion == 1) {
			$current_update_version = 2;
			
			$pdo->exec('BEGIN TRANSACTION');
			// the current version is lower than the expected version
			// Perform database schema upgrades based on version difference
			// Example: Add new columns, modify tables, etc.
			
			// In this case, let's add the 'multiple_subscription' column as before
			$query = "ALTER TABLE events ADD COLUMN multiple_subscription INTEGER DEFAULT 1";
			$pdo->exec($query);
			
			$query = "CREATE VIEW events_last_stats AS SELECT *, (ts_sxb_start - STRFTIME('%s'))/86400.0 AS age FROM events AS E WHERE E.category_id = 1 AND age > -21";
			$pdo->exec($query);
			
			$query = "CREATE VIEW user_subscription_stats AS SELECT U.*, COUNT(U.id) AS total FROM users AS U, subscriptions AS S, events_last_stats AS E WHERE S.event_id = E.id AND U.id = S.user_id GROUP BY U.id ORDER BY total DESC";
			$pdo->exec($query);
			
			$pdo->exec("DELETE FROM schema_version");

			// Update the database version to the expected version
			$pdo->exec("INSERT INTO schema_version (version) VALUES ($current_update_version)");
			echo "Database upgraded to version $current_update_version.";
			
			$pdo->exec('COMMIT');
			$currentVersion = $current_update_version;
		}
		
		if ($currentVersion == 2) {
			$current_update_version = 3;
			
			$pdo->exec('BEGIN TRANSACTION');
			
			// In this case, let's add the 'tense_activity' column as before
			$query = "ALTER TABLE events ADD COLUMN tense_activity Boolean DEFAULT FALSE";
			$pdo->exec($query);
			
			$pdo->exec("DELETE FROM schema_version");
			
			// Update the database version to the expected version
			$pdo->exec("INSERT INTO schema_version (version) VALUES ($current_update_version)");
			echo "Database upgraded to version $current_update_version.";
			
			$pdo->exec('COMMIT');
			$currentVersion = $current_update_version;
		}
	} catch (PDOException $e) {
		
		echo "Error: " . $e->getMessage();
		$pdo->exec('ROLLBACK');
	}
}

performDbUpgrade();