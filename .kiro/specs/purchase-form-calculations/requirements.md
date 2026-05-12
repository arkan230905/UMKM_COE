# Requirements Document

## Introduction

This document specifies the business requirements for enhancing the purchase form at `/transaksi/pembelian/create` to provide accurate calculations, flexible unit conversions, and improved user experience for purchase data entry. The system must handle both raw materials (bahan baku) and supporting materials (bahan pendukung) with comprehensive unit conversion capabilities and real-time calculation feedback.

## Glossary

- **Purchase_Form**: The web form interface for creating purchase transactions
- **Calculation_Engine**: The core system component that processes all mathematical calculations
- **Unit_Converter**: The system component that handles unit conversions between different measurement units
- **Sub_Unit_Manager**: The system component that manages sub-unit calculations and conversions
- **Material_Panel**: The information panel that displays conversion examples and guidelines
- **Item_Table**: The data entry table for purchase items with integrated calculations
- **Conversion_Section**: The form section that handles main unit and sub-unit conversions
- **Header_Section**: The form section containing purchase document information
- **Price_Calculator**: The system component that handles all price-related calculations

## Requirements

### Requirement 1: Purchase Document Management

**User Story:** As a purchasing staff member, I want to capture essential purchase document information, so that I can properly identify and track purchase transactions.

#### Acceptance Criteria

1. WHEN creating a new purchase document, THE Header_Section SHALL display vendor selection with autocomplete search capability
2. WHEN a vendor is selected, THE Header_Section SHALL populate vendor information and filter available materials by vendor type
3. WHEN entering purchase date, THE Header_Section SHALL validate date format and prevent future dates beyond reasonable business limits
4. WHEN the form loads, THE Header_Section SHALL auto-generate a unique document number following organizational numbering conventions
5. WHEN selecting purchase type, THE Header_Section SHALL provide clear options for raw materials (bahan baku) and supporting materials (bahan pendukung)

### Requirement 2: Conversion Guidance and Examples

**User Story:** As a user entering purchase data, I want to see conversion examples and guidelines, so that I can understand how to properly convert between different units.

#### Acceptance Criteria

1. WHEN the purchase form loads, THE Material_Panel SHALL display conversion examples relevant to the selected material type
2. WHEN a material is selected in the item table, THE Material_Panel SHALL update examples to show conversions specific to that material
3. WHEN viewing conversion examples, THE Material_Panel SHALL show step-by-step conversion guidelines with clear formulas
4. WHEN hovering over conversion elements, THE Material_Panel SHALL provide contextual help tooltips explaining the conversion process
5. WHERE space is limited, THE Material_Panel SHALL be collapsible while maintaining easy access to guidance information

### Requirement 3: Purchase Item Data Entry

**User Story:** As a purchasing staff member, I want to enter purchase item details with real-time calculations, so that I can accurately capture purchase quantities, units, and prices.

#### Acceptance Criteria

1. WHEN selecting a material, THE Item_Table SHALL provide autocomplete search functionality across all available materials
2. WHEN entering quantity values, THE Item_Table SHALL validate that quantities are positive numbers and display validation feedback
3. WHEN selecting purchase units, THE Item_Table SHALL display only units that are valid for the selected material
4. WHEN entering either unit price or total price, THE Item_Table SHALL automatically calculate the corresponding value using bidirectional calculation
5. WHEN any item data changes, THE Item_Table SHALL update row totals and form subtotals in real-time
6. WHEN adding or removing rows, THE Item_Table SHALL maintain calculation accuracy and update form totals accordingly

### Requirement 4: Unit Conversion Processing

**User Story:** As a user working with different measurement units, I want to convert purchase units to base units with manual override capability, so that I can handle variations in actual purchase conditions.

#### Acceptance Criteria

1. WHEN an item is added to the purchase table, THE Unit_Converter SHALL automatically calculate conversion from purchase unit to base unit using predefined rates
2. WHEN automatic conversion rates are not available or inappropriate, THE Unit_Converter SHALL allow manual conversion rate input
3. WHEN manual conversion rates are entered, THE Unit_Converter SHALL validate that rates are positive numbers and recalculate accordingly
4. WHEN conversions are processed, THE Unit_Converter SHALL display the conversion formula showing the calculation steps
5. WHEN conversion errors occur, THE Unit_Converter SHALL provide clear error messages and suggest alternative approaches

### Requirement 5: Sub-Unit Calculation Management

**User Story:** As a user dealing with materials that have multiple sub-units, I want to see calculated sub-unit quantities with manual adjustment capability, so that I can account for actual conditions that may differ from standard conversions.

#### Acceptance Criteria

1. WHEN a material has configured sub-units, THE Sub_Unit_Manager SHALL display all available sub-units with their conversion factors from master data
2. WHEN base unit quantities are established, THE Sub_Unit_Manager SHALL automatically calculate sub-unit quantities using configured conversion factors
3. WHEN calculated sub-unit quantities need adjustment, THE Sub_Unit_Manager SHALL allow manual override of calculated values
4. WHEN manual overrides are applied, THE Sub_Unit_Manager SHALL update the conversion formula to reflect the actual conversion rate used
5. WHEN sub-unit calculations are displayed, THE Sub_Unit_Manager SHALL clearly distinguish between calculated and manually adjusted values

### Requirement 6: Real-Time Price Calculations

**User Story:** As a purchasing staff member, I want accurate price calculations that update immediately as I enter data, so that I can see the financial impact of purchase decisions in real-time.

#### Acceptance Criteria

1. WHEN item quantities or unit prices change, THE Price_Calculator SHALL immediately recalculate item totals and display updated values
2. WHEN calculating item totals, THE Price_Calculator SHALL use the formula: total = quantity × unit_price with proper rounding to currency precision
3. WHEN all item totals are calculated, THE Price_Calculator SHALL compute subtotal as the sum of all item totals
4. WHEN shipping costs and tax percentages are provided, THE Price_Calculator SHALL calculate final total including these additional costs
5. WHEN price calculations are performed, THE Price_Calculator SHALL ensure all monetary values are formatted consistently according to local currency standards

### Requirement 7: Calculation Accuracy and Validation

**User Story:** As a system administrator, I want all calculations to be mathematically accurate and validated, so that purchase data integrity is maintained throughout the system.

#### Acceptance Criteria

1. WHEN any calculation is performed, THE Calculation_Engine SHALL validate input parameters to ensure they meet mathematical requirements
2. WHEN unit conversions are processed, THE Calculation_Engine SHALL verify that conversion rates are positive and produce valid results
3. WHEN price calculations involve multiple items, THE Calculation_Engine SHALL ensure subtotals equal the sum of individual item totals
4. WHEN manual overrides are applied, THE Calculation_Engine SHALL validate that override values are within reasonable business ranges
5. WHEN calculation errors are detected, THE Calculation_Engine SHALL prevent form submission and display specific error messages

### Requirement 8: Form State Management and Persistence

**User Story:** As a user working on complex purchase forms, I want my work to be preserved during the session and recoverable if interrupted, so that I don't lose data due to technical issues.

#### Acceptance Criteria

1. WHEN form data is entered, THE Purchase_Form SHALL automatically save draft data at regular intervals during user interaction
2. WHEN the browser is refreshed or accidentally closed, THE Purchase_Form SHALL recover unsaved changes and restore the previous form state
3. WHEN calculation states change, THE Purchase_Form SHALL maintain consistency between all form sections and their displayed values
4. WHEN validation errors occur, THE Purchase_Form SHALL preserve valid data while highlighting and explaining invalid entries
5. WHEN the form is submitted successfully, THE Purchase_Form SHALL clear draft data and provide confirmation of successful processing

### Requirement 9: User Interface Responsiveness

**User Story:** As a user accessing the system from different devices, I want the purchase form to work effectively on desktop, tablet, and mobile devices, so that I can enter purchase data regardless of my device.

#### Acceptance Criteria

1. WHEN accessing the form on desktop devices, THE Purchase_Form SHALL display all sections in an organized layout with adequate spacing for comfortable data entry
2. WHEN accessing the form on tablet devices, THE Purchase_Form SHALL adapt the layout to stack sections vertically while maintaining touch-friendly controls
3. WHEN accessing the form on mobile devices, THE Purchase_Form SHALL provide horizontally scrollable tables and larger touch targets for mobile interaction
4. WHEN screen space is limited, THE Purchase_Form SHALL allow collapsing of guidance sections while keeping essential data entry areas accessible
5. WHEN the form is displayed, THE Purchase_Form SHALL maintain consistent functionality across all supported device types and screen sizes

### Requirement 10: Error Handling and User Feedback

**User Story:** As a user entering purchase data, I want clear feedback when errors occur and guidance on how to correct them, so that I can successfully complete purchase transactions without confusion.

#### Acceptance Criteria

1. WHEN invalid data is entered, THE Purchase_Form SHALL display inline validation messages near the relevant input fields
2. WHEN calculation errors occur, THE Purchase_Form SHALL provide specific error descriptions with suggested corrections
3. WHEN system errors prevent normal operation, THE Purchase_Form SHALL display user-friendly error messages and suggest alternative actions
4. WHEN validation errors are present, THE Purchase_Form SHALL prevent form submission while allowing continued work on valid sections
5. WHEN errors are corrected, THE Purchase_Form SHALL immediately remove error messages and restore normal functionality