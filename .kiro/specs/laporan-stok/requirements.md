# Requirements Document

## Introduction

The Stock Report (Laporan Stok) feature provides comprehensive stock tracking and reporting capabilities for materials, products, and supporting materials. The system displays stock cards with detailed transaction history, unit conversions, and proper stock calculation logic for production and purchase activities.

## Glossary

- **Stock_Report_System**: The main system component that handles stock reporting functionality
- **Stock_Card**: A detailed view showing stock movements for a specific item over time
- **Unit_Converter**: Component responsible for converting quantities between different units of measurement
- **Stock_Movement**: Individual transaction records that affect stock levels
- **Material**: Raw materials used in production (Bahan Baku)
- **Product**: Finished goods produced by the system
- **Supporting_Material**: Additional materials that support production (Bahan Pendukung)
- **Primary_Unit**: The main unit of measurement for an item
- **Sub_Unit**: Alternative units of measurement with conversion ratios to primary unit
- **Opening_Balance**: Stock quantity at the beginning of a period
- **Purchase_Transaction**: Stock increase from procurement activities
- **Production_Transaction**: Stock changes from manufacturing activities
- **Final_Stock**: Calculated stock quantity after all transactions

## Requirements

### Requirement 1: Stock Report Page Access

**User Story:** As an admin or owner, I want to access the stock report page at the specified URL, so that I can view stock information for different items.

#### Acceptance Criteria

1. WHEN a user with admin or owner role accesses "/laporan/stok", THE Stock_Report_System SHALL display the stock report interface
2. THE Stock_Report_System SHALL support query parameters: tipe, item_id, and satuan_id
3. WHEN no parameters are provided, THE Stock_Report_System SHALL display the default filter interface
4. THE Stock_Report_System SHALL restrict access to users with admin or owner roles only

### Requirement 2: Item Type Selection and Filtering

**User Story:** As a user, I want to select different item types and specific items, so that I can view stock data for the items I'm interested in.

#### Acceptance Criteria

1. THE Stock_Report_System SHALL provide a dropdown for selecting item types: Material, Product, or Supporting_Material
2. WHEN an item type is selected, THE Stock_Report_System SHALL populate the item dropdown with relevant items
3. THE Stock_Report_System SHALL filter stock data based on the selected item type and item ID
4. WHEN tipe parameter equals "material", THE Stock_Report_System SHALL display Material items
5. WHEN tipe parameter equals "product", THE Stock_Report_System SHALL display Product items
6. WHEN tipe parameter equals "bahan_pendukung", THE Stock_Report_System SHALL display Supporting_Material items

### Requirement 3: Unit Conversion Display

**User Story:** As a user, I want to view stock data in different units of measurement, so that I can analyze stock in the most appropriate unit for my needs.

#### Acceptance Criteria

1. WHEN an item has multiple units configured, THE Stock_Report_System SHALL display a unit selection dropdown
2. THE Stock_Report_System SHALL show available units: Primary_Unit and up to 3 Sub_Units
3. WHEN a Sub_Unit is selected, THE Stock_Report_System SHALL convert all quantities using the correct conversion ratio
4. THE Unit_Converter SHALL apply the formula: display_quantity = primary_quantity × conversion_ratio
5. THE Stock_Report_System SHALL display the conversion ratio information for non-primary units
6. FOR ALL unit conversions, THE Unit_Converter SHALL maintain mathematical consistency across all calculations

### Requirement 4: Stock Card Data Structure

**User Story:** As a user, I want to see detailed stock cards with proper column structure, so that I can track stock movements accurately.

#### Acceptance Criteria

1. THE Stock_Report_System SHALL display stock cards with columns: Date, Reference, Opening_Balance (Qty/Price/Total), Purchase_Transaction (Qty/Price/Total), Production_Transaction (Qty/Price/Total), Final_Stock (Qty/Price/Total)
2. THE Stock_Report_System SHALL show each transaction as a separate row with individual date entries
3. THE Stock_Report_System SHALL display monthly opening balance rows for multi-month periods
4. WHEN displaying transaction references, THE Stock_Report_System SHALL show transaction type and ID
5. THE Stock_Report_System SHALL format all monetary values with proper currency formatting
6. THE Stock_Report_System SHALL format all quantity values with appropriate decimal precision

### Requirement 5: Stock Calculation Logic

**User Story:** As a user, I want accurate stock calculations that properly account for all transaction types, so that I can trust the stock data for decision making.

#### Acceptance Criteria

1. THE Stock_Report_System SHALL calculate Opening_Balance from all transactions before the selected date range
2. WHEN processing Purchase_Transaction movements, THE Stock_Report_System SHALL add quantities to stock levels
3. WHEN processing Production_Transaction movements for products, THE Stock_Report_System SHALL add quantities to stock levels
4. WHEN processing Production_Transaction movements for materials, THE Stock_Report_System SHALL subtract quantities from stock levels
5. THE Stock_Report_System SHALL maintain running totals that accurately reflect cumulative stock changes
6. FOR ALL stock calculations, THE Stock_Report_System SHALL preserve mathematical accuracy across unit conversions
7. THE Stock_Report_System SHALL handle initial stock entries as Opening_Balance transactions

### Requirement 6: Multi-Unit Stock Cards

**User Story:** As a user, I want to view the same item in different units simultaneously, so that I can compare stock levels across different measurement units.

#### Acceptance Criteria

1. THE Stock_Report_System SHALL support displaying multiple stock cards for the same item in different units
2. WHEN satuan_id parameter is provided, THE Stock_Report_System SHALL display stock data in the specified unit
3. WHEN no satuan_id is provided, THE Stock_Report_System SHALL default to the Primary_Unit
4. THE Stock_Report_System SHALL maintain data consistency across all unit representations of the same item
5. FOR ALL unit variations, THE Stock_Report_System SHALL show identical transaction dates and references

### Requirement 7: Price Calculation Per Unit

**User Story:** As a user, I want to see accurate price per unit calculations for different measurement units, so that I can understand the cost implications of different units.

#### Acceptance Criteria

1. THE Stock_Report_System SHALL calculate price per display unit using the formula: display_unit_price = primary_unit_price ÷ conversion_ratio
2. THE Stock_Report_System SHALL maintain consistent pricing across all transactions for the same item
3. WHEN displaying total values, THE Stock_Report_System SHALL calculate: total = quantity × unit_price
4. THE Stock_Report_System SHALL ensure price consistency between different unit representations
5. FOR ALL price calculations, THE Stock_Report_System SHALL round to appropriate decimal places for currency display

### Requirement 8: Date Range Filtering

**User Story:** As a user, I want to filter stock data by date ranges, so that I can analyze stock movements for specific time periods.

#### Acceptance Criteria

1. THE Stock_Report_System SHALL support optional "from" and "to" date parameters
2. WHEN date range is specified, THE Stock_Report_System SHALL show only transactions within the range
3. WHEN no date range is specified, THE Stock_Report_System SHALL show all historical transactions
4. THE Stock_Report_System SHALL calculate Opening_Balance from all transactions before the "from" date
5. THE Stock_Report_System SHALL display monthly opening balances for multi-month date ranges

### Requirement 9: Error Handling and Data Validation

**User Story:** As a user, I want proper error handling when viewing stock reports, so that I receive clear feedback when issues occur.

#### Acceptance Criteria

1. WHEN invalid item_id is provided, THE Stock_Report_System SHALL display an appropriate error message
2. WHEN invalid satuan_id is provided, THE Stock_Report_System SHALL fall back to Primary_Unit display
3. WHEN no stock data exists for selected criteria, THE Stock_Report_System SHALL display a "No Data" message
4. IF database errors occur, THEN THE Stock_Report_System SHALL log the error and display a user-friendly message
5. THE Stock_Report_System SHALL validate all input parameters before processing

### Requirement 10: Performance and Responsiveness

**User Story:** As a user, I want the stock report to load quickly and display properly on different screen sizes, so that I can access stock information efficiently.

#### Acceptance Criteria

1. THE Stock_Report_System SHALL load stock data within 3 seconds for typical datasets
2. THE Stock_Report_System SHALL use responsive design for mobile and desktop viewing
3. THE Stock_Report_System SHALL implement proper table scrolling for large datasets
4. THE Stock_Report_System SHALL optimize database queries to minimize loading time
5. THE Stock_Report_System SHALL cache frequently accessed conversion ratios for performance