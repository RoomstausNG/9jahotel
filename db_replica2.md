# MySQL Auto Backup and Replication Setup

This guide explains how to:

1. Automatically back up a MySQL database from a remote server.
2. Set up real-time MySQL replication between two VPS servers (Master and Replica).

> **Note**: This setup assumes MySQL is running on a **custom port (e.g., 3307)** and that the database is hosted on a **different server**.

---

## Prerequisites

-   Two VPS servers (Master and Replica)
-   MySQL installed on both
-   SSH access to both servers
-   Custom port number (e.g., 3307)

---

## PART 1: Automatic MySQL Backup

### Step 1: Create Backup Script on Replica Server

Create a file `mysql_remote_backup.sh` on the replica server:

```bash
#!/bin/bash

DATE=$(date +%F-%H-%M)
REMOTE_DB_HOST="remote-host"
REMOTE_DB_PORT=3307
DB_NAME="your_db"
DB_USER="your_user"
DB_PASS="your_password"
BACKUP_DIR="/home/backups"

mkdir -p $BACKUP_DIR
mysqldump -h $REMOTE_DB_HOST -P $REMOTE_DB_PORT -u $DB_USER -p$DB_PASS --single-transaction $DB_NAME | gzip > $BACKUP_DIR/${DB_NAME}_$DATE.sql.gz
```

Make it executable:

```bash
chmod +x mysql_remote_backup.sh
```

### Step 2: Schedule Backup with Cron

```bash
crontab -e
```

Add this line for daily backups at midnight:

```cron
0 0 * * * /home/your_user/mysql_remote_backup.sh
```

---

## PART 2: MySQL Replication Setup (Master ➜ Replica)

### Step 1: Configure Master Server

Edit MySQL config (`/etc/mysql/my.cnf` or `/etc/mysql/mysql.conf.d/mysqld.cnf`):

```ini
[mysqld]
server-id = 1
log_bin = /var/log/mysql/mysql-bin.log
binlog_do_db = your_db
bind-address = 0.0.0.0
port = 3307
```

Restart MySQL:

```bash
sudo systemctl restart mysql
```

### Step 2: Create Replication User

Login to MySQL on the master:

```bash
mysql -u root -p --port=3307
```

```sql
CREATE USER 'replicator'@'%' IDENTIFIED BY 'your_password';
GRANT REPLICATION SLAVE ON *.* TO 'replicator'@'%';
FLUSH PRIVILEGES;
```

### Step 3: Lock Tables and Export

```sql
FLUSH TABLES WITH READ LOCK;
SHOW MASTER STATUS;
```

Note the `File` and `Position` from the output.

Dump the database:

```bash
mysqldump -u root -p --port=3307 --all-databases --master-data > master_dump.sql
```

Unlock tables:

```sql
UNLOCK TABLES;
```

### Step 4: Configure Replica Server

Edit MySQL config on replica:

```ini
[mysqld]
server-id = 2
relay-log = /var/log/mysql/mysql-relay-bin.log
log_bin = /var/log/mysql/mysql-bin.log
port = 3307
```

Restart MySQL:

```bash
sudo systemctl restart mysql
```

### Step 5: Import Master Dump

Transfer the dump to replica:

```bash
scp -P 22 root@master_ip:/path/to/master_dump.sql .
```

Import it:

```bash
mysql -u root -p --port=3307 < master_dump.sql
```

### Step 6: Start Replication

Login to MySQL on replica:

```bash
mysql -u root -p --port=3307
```

```sql
CHANGE MASTER TO
  MASTER_HOST='your_master_ip',
  MASTER_PORT=3307,
  MASTER_USER='replicator',
  MASTER_PASSWORD='your_password',
  MASTER_LOG_FILE='mysql-bin.000001',
  MASTER_LOG_POS=12345;

START SLAVE;
```

Verify:

```sql
SHOW SLAVE STATUS\G
```

Ensure both `Slave_IO_Running` and `Slave_SQL_Running` are `Yes`.

---

## Optional: Replication Health Check Script

Create `check_replication.sh` on replica:

```bash
#!/bin/bash
status=$(mysql -u root -p'your_password' -e "SHOW SLAVE STATUS\G" | grep -E "Slave_IO_Running|Slave_SQL_Running" | grep -c "Yes")

if [ "$status" -ne 2 ]; then
  echo "Replication is broken, restarting..."
  mysql -u root -p'your_password' -e "STOP SLAVE; START SLAVE;"
else
  echo "Replication running fine."
fi
```

Run every 5 minutes via cron:

```cron
*/5 * * * * /home/your_user/check_replication.sh
```

---

## Summary

| Task        | Description                          |
| ----------- | ------------------------------------ |
| Backup      | Automate backup with cron & script   |
| Replication | Real-time sync from Master ➜ Replica |
| Port        | All commands use custom port (3307)  |
| Monitoring  | Optional cron-based health checker   |

---

For further optimization, consider automating remote dump transfers with SSH keys, compressing backups with `gzip`, and adding backup retentio
