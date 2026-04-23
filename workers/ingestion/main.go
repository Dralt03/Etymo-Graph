package main

import (
	"database/sql"
	"fmt"
	"log"
	"os"
	"path/filepath"

	_ "github.com/go-sql-driver/mysql"
	"github.com/joho/godotenv"
)

func main() {

	envPath := findEnvFile()
	if envPath == "" {
		log.Println(".env file not found, using environment default settings")
	} else {
		godotenv.Load(envPath)
	}

	db := connectToDatabase()
	defer db.Close()

	// 3. Setup OpenSearch connection (stubbed for now)
	// ... OpenSearch setup here ...

	// 4. Run the data stream processing
	processWiktionaryDump(db)
}

// connectToDatabase connects to MySQL or SQLite based on Laravel's .env 
func connectToDatabase() *sql.DB {
	connectionType := os.Getenv("DB_CONNECTION")
	if connectionType == "sqlite" {
		log.Fatalf("Error: Go pipeline using go-sql-driver/mysql requires a MySQL database. Found 'sqlite' in .env. Please update DB_CONNECTION to 'mysql' and restart the environment.")
	}

	host := os.Getenv("DB_HOST")
	if host == "" {
		host = "127.0.0.1"
	}
	port := os.Getenv("DB_PORT")
	if port == "" {
		port = "3306"
	}
	user := os.Getenv("DB_USERNAME")
	if user == "" {
		user = "root"
	}
	password := os.Getenv("DB_PASSWORD")
	dbName := os.Getenv("DB_DATABASE")
	
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?parseTime=true", user, password, host, port, dbName)
	fmt.Printf("🗄️  Connecting to MySQL database at: %s:%s\n", host, port)

	db, err := sql.Open("mysql", dsn)
	if err != nil {
		log.Fatalf("Failed to open database: %v", err)
	}

	err = db.Ping()
	if err != nil {
		log.Fatalf("Database connection failed: %v", err)
	}
	
	fmt.Println("Database connection established.")
	return db
}

func processWiktionaryDump(db *sql.DB) {
	// Note: In an actual implementation, you'd use encoding/xml with a Decoder
	// to stream through <page> tags incrementally instead of loading the huge file into RAM.

	/* 
		Example Bulk Insert Skeleton:
		
		// Start a transaction for bulk inserts
		tx, _ := db.Begin()
		stmt, _ := tx.Prepare("INSERT INTO words (lemma, language, pos, source) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE lemma=lemma")
		defer stmt.Close()
		
		for word := range wordChannel {
			stmt.Exec(word.Lemma, word.Language, word.Pos, "wiktionary")
		}
		tx.Commit()
	*/
	
	fmt.Println("Stub: Processed 0 entries from XML dump.")
}

func findEnvFile() string {
	// Look up the directory tree to find .env (from workers/ingestion up to laravel root)
	dir, err := os.Getwd()
	if err != nil {
		return ""
	}
	
	for {
		envPath := filepath.Join(dir, ".env")
		if _, err := os.Stat(envPath); !os.IsNotExist(err) {
			return envPath
		}
		
		parent := filepath.Dir(dir)
		if parent == dir {
			break
		}
		dir = parent
	}
	return ""
}
