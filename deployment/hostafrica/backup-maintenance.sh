#!/bin/bash

# ==============================================
# CHANIA SKILLS FOR AFRICA - BACKUP & MAINTENANCE SCRIPT
# HostAfrica Production Environment
# ==============================================

# Exit on any error
set -e

# Script configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$(dirname "$SCRIPT_DIR")")"
BACKUP_DIR="/home/your_username/backups/chania"
LOG_FILE="/home/your_username/logs/maintenance.log"
DATE=$(date +%Y%m%d_%H%M%S)

# Load environment variables
if [ -f "$PROJECT_ROOT/.env" ]; then
    source "$PROJECT_ROOT/.env"
fi

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ==============================================
# LOGGING FUNCTION
# ==============================================

log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

# ==============================================
# SETUP FUNCTIONS
# ==============================================

setup_directories() {
    log "Setting up backup directories..."
    
    # Create backup directories
    mkdir -p "$BACKUP_DIR/database"
    mkdir -p "$BACKUP_DIR/files"
    mkdir -p "$BACKUP_DIR/logs"
    mkdir -p "$(dirname "$LOG_FILE")"
    
    success "Backup directories created"
}

# ==============================================
# DATABASE BACKUP FUNCTIONS
# ==============================================

backup_database() {
    log "Starting database backup..."
    
    local backup_file="$BACKUP_DIR/database/chania_db_$DATE.sql"
    
    # Get database credentials from environment
    local db_host="${DB_HOST:-localhost}"
    local db_name="${DB_NAME:-chania_db}"
    local db_user="${DB_USER:-chania_user}"
    local db_pass="$DB_PASSWORD"
    
    if [ -z "$db_pass" ]; then
        error "Database password not found in environment variables"
    fi
    
    # Create database backup
    mysqldump --host="$db_host" \
              --user="$db_user" \
              --password="$db_pass" \
              --single-transaction \
              --routines \
              --triggers \
              --events \
              --lock-tables=false \
              "$db_name" > "$backup_file"
    
    # Compress the backup
    gzip "$backup_file"
    backup_file="$backup_file.gz"
    
    # Verify backup file exists and has content
    if [ -f "$backup_file" ] && [ -s "$backup_file" ]; then
        success "Database backup completed: $backup_file"
        
        # Get backup file size
        local backup_size=$(du -h "$backup_file" | cut -f1)
        log "Backup size: $backup_size"
    else
        error "Database backup failed or is empty"
    fi
}

# ==============================================
# FILE BACKUP FUNCTIONS
# ==============================================

backup_files() {
    log "Starting file backup..."
    
    local backup_file="$BACKUP_DIR/files/chania_files_$DATE.tar.gz"
    
    # Define directories to backup
    local dirs_to_backup=(
        "assets"
        "client"
        "admin"
        "shared"
        "uploads"
        ".env"
        "*.php"
    )
    
    # Create file backup
    cd "$PROJECT_ROOT"
    tar -czf "$backup_file" \
        --exclude='logs/*' \
        --exclude='cache/*' \
        --exclude='temp/*' \
        --exclude='.git/*' \
        --exclude='node_modules/*' \
        --exclude='deployment/*' \
        "${dirs_to_backup[@]}" 2>/dev/null || true
    
    # Verify backup file exists and has content
    if [ -f "$backup_file" ] && [ -s "$backup_file" ]; then
        success "File backup completed: $backup_file"
        
        # Get backup file size
        local backup_size=$(du -h "$backup_file" | cut -f1)
        log "Backup size: $backup_size"
    else
        error "File backup failed or is empty"
    fi
}

# ==============================================
# LOG BACKUP FUNCTIONS
# ==============================================

backup_logs() {
    log "Starting log backup..."
    
    local log_backup_file="$BACKUP_DIR/logs/chania_logs_$DATE.tar.gz"
    
    # Find and backup log files
    if find "$PROJECT_ROOT" -name "*.log" -type f | head -1 | grep -q .; then
        find "$PROJECT_ROOT" -name "*.log" -type f -print0 | \
            tar -czf "$log_backup_file" --null -T -
        
        if [ -f "$log_backup_file" ] && [ -s "$log_backup_file" ]; then
            success "Log backup completed: $log_backup_file"
        else
            warning "Log backup failed or no logs found"
        fi
    else
        warning "No log files found to backup"
    fi
}

# ==============================================
# CLEANUP FUNCTIONS
# ==============================================

cleanup_old_backups() {
    log "Cleaning up old backups..."
    
    # Keep last 7 days of database backups
    find "$BACKUP_DIR/database" -name "chania_db_*.sql.gz" -mtime +7 -delete 2>/dev/null || true
    
    # Keep last 14 days of file backups
    find "$BACKUP_DIR/files" -name "chania_files_*.tar.gz" -mtime +14 -delete 2>/dev/null || true
    
    # Keep last 30 days of log backups
    find "$BACKUP_DIR/logs" -name "chania_logs_*.tar.gz" -mtime +30 -delete 2>/dev/null || true
    
    success "Old backup cleanup completed"
}

cleanup_temp_files() {
    log "Cleaning up temporary files..."
    
    # Clean cache directories
    if [ -d "$PROJECT_ROOT/cache" ]; then
        find "$PROJECT_ROOT/cache" -name "*.tmp" -delete 2>/dev/null || true
        find "$PROJECT_ROOT/cache" -name "*.cache" -mtime +1 -delete 2>/dev/null || true
    fi
    
    # Clean temp directories
    if [ -d "$PROJECT_ROOT/temp" ]; then
        find "$PROJECT_ROOT/temp" -type f -mtime +1 -delete 2>/dev/null || true
    fi
    
    # Clean session files (if stored in files)
    if [ -d "$PROJECT_ROOT/sessions" ]; then
        find "$PROJECT_ROOT/sessions" -name "sess_*" -mtime +1 -delete 2>/dev/null || true
    fi
    
    success "Temporary file cleanup completed"
}

# ==============================================
# LOG ROTATION
# ==============================================

rotate_logs() {
    log "Rotating application logs..."
    
    # Find and rotate log files larger than 10MB
    find "$PROJECT_ROOT" -name "*.log" -type f -size +10M | while read -r logfile; do
        if [ -f "$logfile" ]; then
            # Create rotated log name
            local rotated_name="${logfile}.$(date +%Y%m%d)"
            
            # Move and compress the log
            mv "$logfile" "$rotated_name"
            gzip "$rotated_name"
            
            # Create new empty log file with proper permissions
            touch "$logfile"
            chmod 644 "$logfile"
            
            log "Rotated log: $(basename "$logfile")"
        fi
    done
    
    success "Log rotation completed"
}

# ==============================================
# HEALTH CHECKS
# ==============================================

check_disk_space() {
    log "Checking disk space..."
    
    # Check if disk space is above 90%
    local disk_usage=$(df "$PROJECT_ROOT" | awk 'NR==2 {print $5}' | sed 's/%//')
    
    if [ "$disk_usage" -gt 90 ]; then
        error "Disk space is critically low: ${disk_usage}% used"
    elif [ "$disk_usage" -gt 80 ]; then
        warning "Disk space is getting low: ${disk_usage}% used"
    else
        success "Disk space is healthy: ${disk_usage}% used"
    fi
}

check_file_permissions() {
    log "Checking file permissions..."
    
    # Check critical directories have correct permissions
    local issues=0
    
    # Check uploads directory is writable
    if [ -d "$PROJECT_ROOT/uploads" ]; then
        if [ ! -w "$PROJECT_ROOT/uploads" ]; then
            warning "Uploads directory is not writable"
            ((issues++))
        fi
    fi
    
    # Check cache directory is writable
    if [ -d "$PROJECT_ROOT/cache" ]; then
        if [ ! -w "$PROJECT_ROOT/cache" ]; then
            warning "Cache directory is not writable"
            ((issues++))
        fi
    fi
    
    # Check logs directory is writable
    if [ -d "$PROJECT_ROOT/logs" ]; then
        if [ ! -w "$PROJECT_ROOT/logs" ]; then
            warning "Logs directory is not writable"
            ((issues++))
        fi
    fi
    
    if [ $issues -eq 0 ]; then
        success "File permissions check passed"
    else
        warning "Found $issues permission issues"
    fi
}

# ==============================================
# MAIN FUNCTIONS
# ==============================================

show_usage() {
    echo "Usage: $0 [OPTION]"
    echo ""
    echo "Options:"
    echo "  backup      - Create full backup (database + files)"
    echo "  db-backup   - Backup database only"
    echo "  file-backup - Backup files only"
    echo "  cleanup     - Clean up old backups and temp files"
    echo "  maintain    - Run full maintenance (backup + cleanup)"
    echo "  health      - Run health checks"
    echo "  help        - Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 backup"
    echo "  $0 maintain"
    echo "  $0 health"
}

run_backup() {
    log "Starting full backup process..."
    setup_directories
    backup_database
    backup_files
    backup_logs
    success "Full backup completed successfully"
}

run_maintenance() {
    log "Starting maintenance process..."
    setup_directories
    backup_database
    backup_files
    backup_logs
    cleanup_old_backups
    cleanup_temp_files
    rotate_logs
    check_disk_space
    check_file_permissions
    success "Maintenance completed successfully"
}

run_health_check() {
    log "Starting health check..."
    check_disk_space
    check_file_permissions
    success "Health check completed"
}

# ==============================================
# MAIN SCRIPT EXECUTION
# ==============================================

main() {
    case "${1:-help}" in
        "backup")
            run_backup
            ;;
        "db-backup")
            setup_directories
            backup_database
            ;;
        "file-backup")
            setup_directories
            backup_files
            backup_logs
            ;;
        "cleanup")
            cleanup_old_backups
            cleanup_temp_files
            rotate_logs
            ;;
        "maintain")
            run_maintenance
            ;;
        "health")
            run_health_check
            ;;
        "help"|*)
            show_usage
            ;;
    esac
}

# Check if running as root (not recommended)
if [ "$EUID" -eq 0 ]; then
    warning "Running as root is not recommended for this script"
fi

# Run main function with all arguments
main "$@"

# ==============================================
# CRON JOB SETUP INSTRUCTIONS
# ==============================================
#
# To set up automated maintenance, add these lines to your crontab:
#
# # Daily database backup at 2 AM
# 0 2 * * * /path/to/backup-maintenance.sh db-backup
#
# # Weekly full maintenance on Sunday at 3 AM
# 0 3 * * 0 /path/to/backup-maintenance.sh maintain
#
# # Daily health check at 6 AM
# 0 6 * * * /path/to/backup-maintenance.sh health
#
# To edit crontab: crontab -e
#
# ==============================================
