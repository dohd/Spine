<!DOCTYPE html>
<html>
<head>
    <title>Payslip</title>
</head>
<body>
    <h1>Employee Payslip</h1>

    <p>Employee Name: {{ $payroll_items->employee->first_name }}</p>
    <p>Employee ID: {{ $payroll_items->employee_id }}</p>
    <!-- Include other relevant payroll_items details -->

    <table>
        <thead>
            <tr>
                <th>Earnings</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td>{{ $payroll_items->basic_pay }}</td>
            </tr>
            <!-- Add other earning components -->
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Deductions</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>PAYE</td>
                <td>{{ $payroll_items->paye }}</td>
            </tr>
            <!-- Add other deduction components -->
        </tbody>
    </table>

    <p>Total Earnings: {{ $payroll_items->total_benefits }}</p>
    <p>Total Deductions: {{ $payroll_items->total_other_deduction }}</p>
    <p>Net Salary: {{ $payroll_items->netpay }}</p>
</body>
</html>
