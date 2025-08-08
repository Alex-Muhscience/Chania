#!/bin/bash

# ==============================================
# CHANIA SKILLS FOR AFRICA - SSL CERTIFICATE MONITOR
# HostAfrica Production Environment
# ==============================================

# Script configuration
DOMAIN="${1:-yourdomain.com}"
EMAIL_ALERT="${2:-admin@yourdomain.com}"
DAYS_WARNING=30
DAYS_CRITICAL=7
LOG_FILE="/home/your_username/logs/ssl-monitor.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ==============================================
# LOGGING FUNCTIONS
# ==============================================

log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

# ==============================================
# SSL CERTIFICATE FUNCTIONS
# ==============================================

check_ssl_expiry() {
    local domain="$1"
    
    log "Checking SSL certificate for $domain..."
    
    # Get certificate expiry date
    local cert_info=$(echo | openssl s_client -servername "$domain" -connect "$domain:443" 2>/dev/null | openssl x509 -noout -dates 2>/dev/null)
    
    if [ -z "$cert_info" ]; then
        error "Failed to retrieve SSL certificate for $domain"
        return 1
    fi
    
    # Extract expiry date
    local expiry_date=$(echo "$cert_info" | grep "notAfter=" | cut -d'=' -f2)
    
    if [ -z "$expiry_date" ]; then
        error "Failed to parse SSL certificate expiry date"
        return 1
    fi
    
    # Convert expiry date to epoch
    local expiry_epoch=$(date -d "$expiry_date" +%s 2>/dev/null)
    
    if [ -z "$expiry_epoch" ]; then
        error "Failed to convert expiry date to epoch: $expiry_date"
        return 1
    fi
    
    # Calculate days until expiry
    local current_epoch=$(date +%s)
    local days_until_expiry=$(( (expiry_epoch - current_epoch) / 86400 ))
    
    # Check certificate status
    if [ $days_until_expiry -le 0 ]; then
        error "SSL certificate for $domain has EXPIRED!"
        send_alert_email "$domain" "EXPIRED" "$days_until_expiry" "$expiry_date"
        return 2
    elif [ $days_until_expiry -le $DAYS_CRITICAL ]; then
        error "SSL certificate for $domain expires in $days_until_expiry days (CRITICAL)"
        send_alert_email "$domain" "CRITICAL" "$days_until_expiry" "$expiry_date"
        return 2
    elif [ $days_until_expiry -le $DAYS_WARNING ]; then
        warning "SSL certificate for $domain expires in $days_until_expiry days"
        send_alert_email "$domain" "WARNING" "$days_until_expiry" "$expiry_date"
        return 1
    else
        success "SSL certificate for $domain is valid for $days_until_expiry more days"
        log "Certificate expires on: $expiry_date"
        return 0
    fi
}

get_certificate_info() {
    local domain="$1"
    
    log "Getting detailed certificate information for $domain..."
    
    # Get full certificate details
    local cert_details=$(echo | openssl s_client -servername "$domain" -connect "$domain:443" 2>/dev/null | openssl x509 -noout -text 2>/dev/null)
    
    if [ -z "$cert_details" ]; then
        error "Failed to retrieve certificate details"
        return 1
    fi
    
    # Extract key information
    local issuer=$(echo "$cert_details" | grep "Issuer:" | sed 's/.*Issuer: //')
    local subject=$(echo "$cert_details" | grep "Subject:" | sed 's/.*Subject: //')
    local san=$(echo "$cert_details" | grep -A1 "Subject Alternative Name:" | tail -1 | sed 's/.*DNS://' | sed 's/, DNS:/, /g')
    local not_before=$(echo "$cert_details" | grep "Not Before:" | sed 's/.*Not Before: //')
    local not_after=$(echo "$cert_details" | grep "Not After :" | sed 's/.*Not After : //')
    
    log "Certificate Details:"
    log "  Subject: $subject"
    log "  Issuer: $issuer"
    log "  Valid From: $not_before"
    log "  Valid Until: $not_after"
    if [ -n "$san" ]; then
        log "  Subject Alternative Names: $san"
    fi
}

check_certificate_chain() {
    local domain="$1"
    
    log "Checking certificate chain for $domain..."
    
    # Verify certificate chain
    local chain_result=$(echo | openssl s_client -servername "$domain" -connect "$domain:443" -verify_return_error 2>&1 | grep "verify return")
    
    if echo "$chain_result" | grep -q "verify return:1"; then
        success "Certificate chain is valid"
    else
        warning "Certificate chain may have issues: $chain_result"
    fi
}

check_ssl_labs_grade() {
    local domain="$1"
    
    log "Checking SSL Labs grade for $domain (this may take a while)..."
    
    # Note: This would require SSL Labs API integration
    # For now, we'll just provide instructions
    log "To check SSL Labs grade manually:"
    log "Visit: https://www.ssllabs.com/ssltest/analyze.html?d=$domain"
}

# ==============================================
# ALERT FUNCTIONS
# ==============================================

send_alert_email() {
    local domain="$1"
    local status="$2"
    local days="$3"
    local expiry_date="$4"
    
    # Check if mail command is available
    if ! command -v mail >/dev/null 2>&1; then
        warning "Mail command not available, cannot send email alerts"
        return 1
    fi
    
    local subject="[SSL ALERT] Certificate $status for $domain"
    local body="SSL Certificate Alert for $domain

Status: $status
Days until expiry: $days
Expiry date: $expiry_date

Please renew the SSL certificate as soon as possible.

This is an automated message from the Chania Skills for Africa SSL monitor.
Server: $(hostname)
Timestamp: $(date)"

    echo "$body" | mail -s "$subject" "$EMAIL_ALERT"
    
    if [ $? -eq 0 ]; then
        log "Alert email sent to $EMAIL_ALERT"
    else
        error "Failed to send alert email"
    fi
}

# ==============================================
# CERTIFICATE RENEWAL HELPER
# ==============================================

suggest_renewal_method() {
    local domain="$1"
    
    log "SSL Certificate Renewal Suggestions for $domain:"
    log ""
    log "1. Let's Encrypt (Free):"
    log "   - Install certbot: sudo apt-get install certbot"
    log "   - Get certificate: sudo certbot certonly --webroot -w /path/to/webroot -d $domain"
    log "   - Auto-renewal: Add to crontab: 0 12 * * * /usr/bin/certbot renew --quiet"
    log ""
    log "2. HostAfrica cPanel SSL:"
    log "   - Login to cPanel"
    log "   - Go to SSL/TLS section"
    log "   - Use Let's Encrypt or upload custom certificate"
    log ""
    log "3. CloudFlare SSL (if using CloudFlare):"
    log "   - Login to CloudFlare dashboard"
    log "   - Go to SSL/TLS settings"
    log "   - Enable Full (strict) mode"
    log ""
    log "4. Commercial SSL Certificate:"
    log "   - Purchase from provider (DigiCert, Comodo, etc.)"
    log "   - Generate CSR and install certificate"
    log ""
}

# ==============================================
# MONITORING FUNCTIONS
# ==============================================

create_monitoring_report() {
    local domain="$1"
    local report_file="/tmp/ssl-report-$domain-$(date +%Y%m%d).txt"
    
    log "Creating SSL monitoring report for $domain..."
    
    {
        echo "SSL Certificate Monitoring Report"
        echo "================================="
        echo "Domain: $domain"
        echo "Generated: $(date)"
        echo "Server: $(hostname)"
        echo ""
        
        # Check certificate
        if check_ssl_expiry "$domain" >/dev/null 2>&1; then
            echo "✓ Certificate Status: Valid"
        else
            echo "✗ Certificate Status: Issues detected"
        fi
        
        echo ""
        get_certificate_info "$domain" 2>/dev/null | grep -E "(Subject:|Issuer:|Valid From:|Valid Until:|Subject Alternative Names:)"
        
        echo ""
        echo "Recommendations:"
        echo "- Monitor certificate expiry regularly"
        echo "- Set up automatic renewal if possible"
        echo "- Keep intermediate certificates updated"
        echo "- Test SSL configuration periodically"
        
    } > "$report_file"
    
    log "Report saved to: $report_file"
}

# ==============================================
# MAIN FUNCTIONS
# ==============================================

show_usage() {
    echo "Usage: $0 [domain] [alert_email]"
    echo ""
    echo "Arguments:"
    echo "  domain      - Domain name to check (default: yourdomain.com)"
    echo "  alert_email - Email for alerts (default: admin@yourdomain.com)"
    echo ""
    echo "Options:"
    echo "  check       - Check SSL certificate status"
    echo "  info        - Show detailed certificate information"
    echo "  monitor     - Run monitoring check with alerts"
    echo "  report      - Generate monitoring report"
    echo "  help        - Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 check example.com admin@example.com"
    echo "  $0 info example.com"
    echo "  $0 monitor"
    echo ""
    echo "Environment Variables:"
    echo "  DAYS_WARNING  - Days before expiry to warn (default: 30)"
    echo "  DAYS_CRITICAL - Days before expiry to alert (default: 7)"
}

run_check() {
    local domain="$1"
    local exit_code=0
    
    # Create log directory
    mkdir -p "$(dirname "$LOG_FILE")"
    
    log "Starting SSL certificate check for $domain"
    
    # Check certificate expiry
    check_ssl_expiry "$domain"
    local cert_status=$?
    
    if [ $cert_status -gt $exit_code ]; then
        exit_code=$cert_status
    fi
    
    # Check certificate chain
    check_certificate_chain "$domain"
    
    log "SSL check completed with exit code: $exit_code"
    return $exit_code
}

run_info() {
    local domain="$1"
    
    mkdir -p "$(dirname "$LOG_FILE")"
    
    log "Getting SSL certificate information for $domain"
    get_certificate_info "$domain"
    check_certificate_chain "$domain"
    suggest_renewal_method "$domain"
}

run_monitor() {
    local domain="$1"
    
    mkdir -p "$(dirname "$LOG_FILE")"
    
    log "Running SSL monitoring for $domain"
    
    # Run the check
    if run_check "$domain"; then
        success "SSL monitoring completed successfully"
    else
        error "SSL monitoring detected issues"
    fi
}

run_report() {
    local domain="$1"
    
    mkdir -p "$(dirname "$LOG_FILE")"
    create_monitoring_report "$domain"
}

# ==============================================
# MAIN SCRIPT EXECUTION
# ==============================================

main() {
    local action="${1:-check}"
    local domain="$DOMAIN"
    
    case "$action" in
        "check")
            run_check "$domain"
            ;;
        "info")
            run_info "$domain"
            ;;
        "monitor")
            run_monitor "$domain"
            ;;
        "report")
            run_report "$domain"
            ;;
        "help"|*)
            show_usage
            ;;
    esac
}

# Validate domain parameter
if [ -z "$DOMAIN" ] || [ "$DOMAIN" = "yourdomain.com" ]; then
    echo "Please specify a valid domain name"
    show_usage
    exit 1
fi

# Run main function
main "$@"

# ==============================================
# CRON JOB SETUP INSTRUCTIONS
# ==============================================
#
# To set up automated SSL monitoring, add these lines to your crontab:
#
# # Daily SSL certificate check at 8 AM
# 0 8 * * * /path/to/ssl-monitor.sh monitor your-domain.com admin@yourdomain.com
#
# # Weekly detailed report on Monday at 9 AM
# 0 9 * * 1 /path/to/ssl-monitor.sh report your-domain.com
#
# To edit crontab: crontab -e
#
# ==============================================
