# Currency Conversion System - KSH to USD

## Overview
The currency conversion system has been implemented to allow admin users to input program fees in Kenyan Shillings (KSH) while storing the equivalent in US Dollars (USD) in the database. The conversion rate is fixed at:

**1 USD = 100 KSH**

## Key Features

### 1. Admin Panel Currency Input
- **Primary Currency**: Kenyan Shillings (KSH)
- **Storage Currency**: US Dollars (USD)
- **Live Conversion**: Real-time KSH to USD conversion display
- **Dual Display**: Shows both KSH and USD values for clarity

### 2. Client-Side Display
- **Primary Display**: KSH amounts (more relevant for Kenyan users)
- **Secondary Display**: USD equivalent in parentheses
- **Example**: "KSh 50,000 ($500.00)"

## Files Modified

### Backend Files

#### 1. CurrencyConverter Class (`/shared/Core/CurrencyConverter.php`)
- **Purpose**: Core currency conversion utility
- **Key Methods**:
  - `usdToKsh($usdAmount)` - Convert USD to KSH
  - `kshToUsd($kshAmount)` - Convert KSH to USD
  - `formatKsh($amount)` - Format KSH display
  - `formatUsd($amount)` - Format USD display
  - `getDualDisplay($usdAmount, $primaryCurrency)` - Dual currency display
  - `validateAndConvertKshInput($kshInput)` - Validation and conversion
  - `getCurrencyInfo($usdAmount)` - Complete currency information
  - `getJavaScriptConverter()` - Client-side JavaScript functions

#### 2. Program Management Files
- **`/admin/public/program_add.php`**: 
  - Updated to accept KSH input
  - Converts to USD before database storage
  - Live conversion preview
  
- **`/admin/public/program_edit.php`**: 
  - Displays stored USD as KSH for editing
  - Converts updated KSH back to USD
  - Shows current stored value for reference

### Frontend Features

#### 1. Live Conversion Display
```javascript
// Real-time conversion as user types
function updateCurrencyConversion() {
    const feeKshInput = document.getElementById('fee_ksh');
    const usdConversionDiv = document.getElementById('usd_conversion');
    
    CurrencyConverter.updateLiveConversion(feeKshInput, usdConversionDiv, 'KSH');
}
```

#### 2. Enhanced Admin Interface
- **Input Field**: KSH with primary currency indicator
- **Live Preview**: Shows USD equivalent as user types
- **Current Value Display**: Shows existing stored value in both currencies
- **Validation**: Prevents negative values and validates input

## Database Impact

### Storage Method
- **Field**: `programs.fee` (decimal/float)
- **Stored As**: USD values (converted from KSH input)
- **Example**: KSH 50,000 input → $500.00 stored

### Backward Compatibility
- Existing USD values remain unchanged
- New entries automatically convert from KSH input
- Client-side display works with both old and new entries

## Usage Examples

### Admin Panel - Adding a Program
1. **User inputs**: KSH 25,000
2. **Live display shows**: "Equivalent to $250.00 USD"
3. **Database stores**: 250.00 (USD)
4. **Client displays**: "KSh 25,000 ($250.00)"

### Admin Panel - Editing a Program
1. **Database has**: $300.00 (USD)
2. **Form displays**: KSH 30,000 (converted for editing)
3. **Shows current**: "Current: KSh 30,000 ($300.00)"
4. **User can modify**: KSH amount with live USD preview

## Client-Side Display Examples

### Program Details Page
```php
<?php
$currencyInfo = CurrencyConverter::getCurrencyInfo($program['fee']);
echo $currencyInfo['dual_display_ksh_first']; // "KSh 50,000 ($500.00)"
?>
```

### Program Cards/Lists
```php
<?php if ($program['fee'] > 0): ?>
    <span class="price">
        <?= CurrencyConverter::getDualDisplay($program['fee'], 'KSH') ?>
    </span>
<?php else: ?>
    <span class="price text-success">FREE</span>
<?php endif; ?>
```

## Validation and Error Handling

### Input Validation
- **Negative Values**: Prevented with error message
- **Maximum Limit**: 10,000,000 KSH (100,000 USD)
- **Format Validation**: Ensures proper numeric input

### Error Messages
- "Fee cannot be negative"
- "Fee amount is too large"
- Validation integrated with existing form error handling

## JavaScript Integration

### Client-Side Converter
```javascript
const CurrencyConverter = {
    USD_TO_KSH_RATE: 100,
    
    usdToKsh: function(usdAmount) {
        return parseFloat(usdAmount) * this.USD_TO_KSH_RATE;
    },
    
    kshToUsd: function(kshAmount) {
        return parseFloat(kshAmount) / this.USD_TO_KSH_RATE;
    },
    
    formatKsh: function(amount, showSymbol = true) {
        // Returns formatted KSH amount
    },
    
    updateLiveConversion: function(inputElement, displayElement, fromCurrency = 'KSH') {
        // Updates display in real-time
    }
};
```

## Migration Notes

### Existing Data
- **No migration required**: Existing USD values work seamlessly
- **New entries**: Will be in KSH input → USD storage format
- **Mixed compatibility**: System handles both scenarios

### Testing Scenarios
1. **New Program**: Input KSH 50,000 → Should store $500.00
2. **Edit Existing**: $300.00 program → Should show KSH 30,000 for editing
3. **Client Display**: All programs show KSH primary with USD secondary
4. **Free Programs**: KSH 0 input → $0.00 storage → "FREE" display

## Rate Management

### Current Rate
- **Fixed Rate**: 1 USD = 100 KSH
- **Defined in**: `CurrencyConverter::USD_TO_KSH_RATE` constant

### Future Rate Updates
To change the conversion rate:
1. Update `USD_TO_KSH_RATE` constant in `CurrencyConverter` class
2. JavaScript rate automatically syncs from PHP constant
3. **Note**: Only affects new entries; existing stored USD values unchanged

## Benefits

1. **User-Friendly**: Kenyan admins input in familiar KSH
2. **International Compatibility**: USD storage for global consistency  
3. **Clear Display**: Both currencies shown to users
4. **Live Feedback**: Real-time conversion preview
5. **Backward Compatible**: Works with existing data
6. **Flexible**: Easy to modify rate if needed

## Support and Maintenance

### Regular Tasks
- Monitor conversion accuracy
- Update rate if exchange rate policy changes
- Test form validation with various inputs

### Troubleshooting
- Check JavaScript console for conversion errors
- Verify CurrencyConverter class is loaded
- Ensure proper numeric formatting in database
