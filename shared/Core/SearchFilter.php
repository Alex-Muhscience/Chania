<?php
/**
 * Advanced Search and Filter Utility for API endpoints
 */
class SearchFilter {
    private $allowedOperators = ['=', '!=', '>', '<', '>=', '<=', 'LIKE', 'IN', 'NOT IN', 'IS NULL', 'IS NOT NULL'];
    private $query;
    private $params;
    private $whereConditions;
    
    public function __construct($baseQuery = '', $baseParams = []) {
        $this->query = $baseQuery;
        $this->params = $baseParams;
        $this->whereConditions = [];
    }
    
    /**
     * Add a search condition
     */
    public function addCondition($field, $operator, $value = null) {
        if (!in_array(strtoupper($operator), $this->allowedOperators)) {
            throw new InvalidArgumentException("Invalid operator: {$operator}");
        }
        
        $operator = strtoupper($operator);
        
        switch ($operator) {
            case 'LIKE':
                $this->whereConditions[] = "{$field} LIKE ?";
                $this->params[] = "%{$value}%";
                break;
                
            case 'IN':
            case 'NOT IN':
                if (!is_array($value)) {
                    throw new InvalidArgumentException("Value must be an array for {$operator} operator");
                }
                $placeholders = str_repeat('?,', count($value) - 1) . '?';
                $this->whereConditions[] = "{$field} {$operator} ({$placeholders})";
                $this->params = array_merge($this->params, $value);
                break;
                
            case 'IS NULL':
            case 'IS NOT NULL':
                $this->whereConditions[] = "{$field} {$operator}";
                break;
                
            default:
                $this->whereConditions[] = "{$field} {$operator} ?";
                $this->params[] = $value;
                break;
        }
        
        return $this;
    }
    
    /**
     * Add full-text search across multiple fields
     */
    public function addTextSearch($fields, $searchTerm) {
        if (empty($fields) || empty($searchTerm)) {
            return $this;
        }
        
        $searchConditions = [];
        foreach ($fields as $field) {
            $searchConditions[] = "{$field} LIKE ?";
            $this->params[] = "%{$searchTerm}%";
        }
        
        $this->whereConditions[] = '(' . implode(' OR ', $searchConditions) . ')';
        return $this;
    }
    
    /**
     * Add date range filter
     */
    public function addDateRange($field, $startDate = null, $endDate = null) {
        if ($startDate) {
            $this->whereConditions[] = "{$field} >= ?";
            $this->params[] = $startDate;
        }
        
        if ($endDate) {
            $this->whereConditions[] = "{$field} <= ?";
            $this->params[] = $endDate . ' 23:59:59';
        }
        
        return $this;
    }
    
    /**
     * Add ordering
     */
    public function addOrder($field, $direction = 'ASC') {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        
        if (strpos($this->query, 'ORDER BY') !== false) {
            $this->query .= ", {$field} {$direction}";
        } else {
            $this->query .= " ORDER BY {$field} {$direction}";
        }
        
        return $this;
    }
    
    /**
     * Build the final query
     */
    public function build() {
        if (!empty($this->whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $this->whereConditions);
            
            // Insert WHERE clause before ORDER BY if it exists
            if (strpos($this->query, 'ORDER BY') !== false) {
                $this->query = str_replace('ORDER BY', $whereClause . ' ORDER BY', $this->query);
            } else {
                $this->query .= ' ' . $whereClause;
            }
        }
        
        return [
            'query' => $this->query,
            'params' => $this->params
        ];
    }
    
    /**
     * Parse request filters from query parameters
     */
    public static function parseRequestFilters($requestQuery) {
        $filters = [];
        
        // Standard filters
        $standardFilters = ['status', 'category', 'priority', 'is_active'];
        foreach ($standardFilters as $filter) {
            if (isset($requestQuery[$filter]) && $requestQuery[$filter] !== '') {
                $filters[$filter] = $requestQuery[$filter];
            }
        }
        
        // Date range filters
        if (isset($requestQuery['date_from']) && $requestQuery['date_from'] !== '') {
            $filters['date_from'] = $requestQuery['date_from'];
        }
        
        if (isset($requestQuery['date_to']) && $requestQuery['date_to'] !== '') {
            $filters['date_to'] = $requestQuery['date_to'];
        }
        
        // Search term
        if (isset($requestQuery['search']) && $requestQuery['search'] !== '') {
            $filters['search'] = trim($requestQuery['search']);
        }
        
        // Sort options
        if (isset($requestQuery['sort_by']) && $requestQuery['sort_by'] !== '') {
            $filters['sort_by'] = $requestQuery['sort_by'];
        }
        
        if (isset($requestQuery['sort_order']) && $requestQuery['sort_order'] !== '') {
            $filters['sort_order'] = strtoupper($requestQuery['sort_order']);
        }
        
        return $filters;
    }
    
    /**
     * Sanitize field names to prevent SQL injection
     */
    public static function sanitizeFieldName($fieldName) {
        // Only allow alphanumeric characters, underscores, and dots (for table.field)
        return preg_replace('/[^a-zA-Z0-9_.]/', '', $fieldName);
    }
}
?>
