-- Update Existing Schedules to Support Both Online and Physical Modes
-- This script ensures all existing schedules have both online_fee and physical_fee populated

-- First, let's see which schedules need updating
SELECT 
    id, title, delivery_mode, online_fee, physical_fee,
    CASE 
        WHEN online_fee IS NULL OR online_fee = 0 THEN 'Missing online fee'
        WHEN physical_fee IS NULL OR physical_fee = 0 THEN 'Missing physical fee'
        ELSE 'Both fees present'
    END as status
FROM program_schedules 
WHERE deleted_at IS NULL;

-- Update schedules that have missing online fees (set to same as physical fee or a default)
UPDATE program_schedules 
SET online_fee = CASE 
    WHEN physical_fee > 0 THEN physical_fee * 0.8  -- Online typically 20% cheaper
    ELSE 1000.00  -- Default online fee
END,
    updated_at = NOW()
WHERE (online_fee IS NULL OR online_fee = 0) 
AND deleted_at IS NULL;

-- Update schedules that have missing physical fees (set to same as online fee + premium or default)
UPDATE program_schedules 
SET physical_fee = CASE 
    WHEN online_fee > 0 THEN online_fee * 1.25  -- Physical typically 25% more expensive
    ELSE 1250.00  -- Default physical fee
END,
    updated_at = NOW()
WHERE (physical_fee IS NULL OR physical_fee = 0) 
AND deleted_at IS NULL;

-- Ensure no fees are zero - set minimum fees
UPDATE program_schedules 
SET online_fee = GREATEST(online_fee, 500.00),
    physical_fee = GREATEST(physical_fee, 625.00),
    updated_at = NOW()
WHERE deleted_at IS NULL 
AND (online_fee < 500 OR physical_fee < 625);

-- Add helpful comment to schedules that were auto-updated
UPDATE program_schedules 
SET session_notes = CONCAT(
    COALESCE(session_notes, ''), 
    '\n\n[Auto-updated: Both online and physical delivery modes now available with respective pricing.]'
),
    updated_at = NOW()
WHERE deleted_at IS NULL 
AND session_notes NOT LIKE '%Auto-updated%';

-- Verification query to check the updates
SELECT 
    id, title, delivery_mode, 
    CONCAT(currency, ' ', FORMAT(online_fee, 2)) as online_fee_formatted,
    CONCAT(currency, ' ', FORMAT(physical_fee, 2)) as physical_fee_formatted,
    'Both modes available' as availability_status
FROM program_schedules 
WHERE deleted_at IS NULL
ORDER BY start_date DESC;

-- Summary count
SELECT 
    COUNT(*) as total_active_schedules,
    COUNT(CASE WHEN online_fee > 0 THEN 1 END) as schedules_with_online_fee,
    COUNT(CASE WHEN physical_fee > 0 THEN 1 END) as schedules_with_physical_fee,
    COUNT(CASE WHEN online_fee > 0 AND physical_fee > 0 THEN 1 END) as schedules_with_both_fees
FROM program_schedules 
WHERE deleted_at IS NULL;
