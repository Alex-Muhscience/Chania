<?php

/**
 * Currency Conversion Utility Class
 * Handles conversion between Kenyan Shillings (KSH) and US Dollars (USD)
 * Rate: 1 USD = 100 KSH
 */
class CurrencyConverter
{
    // Fixed exchange rate: 1 USD = 100 KSH
    const USD_TO_KSH_RATE = 100;
    
    /**
     * Convert USD to KSH
     * @param float $usdAmount Amount in USD
     * @return float Amount in KSH
     */
    public static function usdToKsh($usdAmount)
    {
        return floatval($usdAmount) * self::USD_TO_KSH_RATE;
    }
    
    /**
     * Convert KSH to USD
     * @param float $kshAmount Amount in KSH
     * @return float Amount in USD
     */
    public static function kshToUsd($kshAmount)
    {
        return floatval($kshAmount) / self::USD_TO_KSH_RATE;
    }
    
    /**
     * Format currency display for KSH
     * @param float $amount Amount in KSH
     * @param bool $showSymbol Whether to show currency symbol
     * @return string Formatted KSH amount
     */
    public static function formatKsh($amount, $showSymbol = true)
    {
        $formatted = number_format(floatval($amount), 0);
        return $showSymbol ? "KSh " . $formatted : $formatted;
    }
    
    /**
     * Format currency display for USD
     * @param float $amount Amount in USD
     * @param bool $showSymbol Whether to show currency symbol
     * @return string Formatted USD amount
     */
    public static function formatUsd($amount, $showSymbol = true)
    {
        $formatted = number_format(floatval($amount), 2);
        return $showSymbol ? "$" . $formatted : $formatted;
    }
    
    /**
     * Get dual currency display
     * @param float $usdAmount Amount in USD (stored value)
     * @param string $primaryCurrency Primary currency to show first ('USD' or 'KSH')
     * @return string Dual currency display string
     */
    public static function getDualDisplay($usdAmount, $primaryCurrency = 'KSH')
    {
        $usd = floatval($usdAmount);
        $ksh = self::usdToKsh($usd);
        
        if ($usd <= 0) {
            return 'FREE';
        }
        
        if ($primaryCurrency === 'KSH') {
            return self::formatKsh($ksh) . ' (' . self::formatUsd($usd) . ')';
        } else {
            return self::formatUsd($usd) . ' (KSh ' . number_format($ksh, 0) . ')';
        }
    }
    
    /**
     * Validate KSH input and convert to USD for storage
     * @param mixed $kshInput Input value in KSH
     * @return array ['valid' => bool, 'usd_value' => float, 'ksh_value' => float, 'error' => string|null]
     */
    public static function validateAndConvertKshInput($kshInput)
    {
        $result = [
            'valid' => false,
            'usd_value' => 0,
            'ksh_value' => 0,
            'error' => null
        ];
        
        // Convert to float and validate
        $kshAmount = floatval($kshInput);
        
        if ($kshAmount < 0) {
            $result['error'] = 'Fee cannot be negative';
            return $result;
        }
        
        if ($kshAmount > 10000000) { // 10 million KSH limit
            $result['error'] = 'Fee amount is too large';
            return $result;
        }
        
        $result['valid'] = true;
        $result['ksh_value'] = $kshAmount;
        $result['usd_value'] = self::kshToUsd($kshAmount);
        
        return $result;
    }
    
    /**
     * Get currency conversion info for display
     * @param float $usdAmount Stored USD amount
     * @return array Currency information array
     */
    public static function getCurrencyInfo($usdAmount)
    {
        $usd = floatval($usdAmount);
        $ksh = self::usdToKsh($usd);
        
        return [
            'usd_amount' => $usd,
            'ksh_amount' => $ksh,
            'usd_formatted' => self::formatUsd($usd),
            'ksh_formatted' => self::formatKsh($ksh),
            'is_free' => $usd <= 0,
            'dual_display_ksh_first' => self::getDualDisplay($usd, 'KSH'),
            'dual_display_usd_first' => self::getDualDisplay($usd, 'USD')
        ];
    }
    
    /**
     * Get JavaScript conversion functions
     * @return string JavaScript code for client-side conversion
     */
    public static function getJavaScriptConverter()
    {
        return "
        const CurrencyConverter = {
            USD_TO_KSH_RATE: " . self::USD_TO_KSH_RATE . ",
            
            usdToKsh: function(usdAmount) {
                return parseFloat(usdAmount) * this.USD_TO_KSH_RATE;
            },
            
            kshToUsd: function(kshAmount) {
                return parseFloat(kshAmount) / this.USD_TO_KSH_RATE;
            },
            
            formatKsh: function(amount, showSymbol = true) {
                const formatted = parseFloat(amount).toLocaleString('en-KE', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
                return showSymbol ? 'KSh ' + formatted : formatted;
            },
            
            formatUsd: function(amount, showSymbol = true) {
                const formatted = parseFloat(amount).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                return showSymbol ? '$' + formatted : formatted;
            },
            
            updateLiveConversion: function(inputElement, displayElement, fromCurrency = 'KSH') {
                const amount = parseFloat(inputElement.value) || 0;
                let convertedAmount, displayText;
                
                if (fromCurrency === 'KSH') {
                    convertedAmount = this.kshToUsd(amount);
                    displayText = amount > 0 ? 
                        'Equivalent to ' + this.formatUsd(convertedAmount) + ' USD' : 
                        'Enter amount in KSH';
                } else {
                    convertedAmount = this.usdToKsh(amount);
                    displayText = amount > 0 ? 
                        'Equivalent to ' + this.formatKsh(convertedAmount) + ' KSH' : 
                        'Enter amount in USD';
                }
                
                displayElement.textContent = displayText;
                displayElement.className = amount > 0 ? 'text-success small' : 'text-muted small';
            }
        };
        ";
    }
}
?>
