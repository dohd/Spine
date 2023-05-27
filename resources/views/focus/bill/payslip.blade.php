<!DOCTYPE html>
<html>
<head>
    <title>Payslip</title>
</head>
<body>
    <h1>Employee Payslip</h1>

    <p>Employee Name: {{ $user->employee->first_name }}</p>
    <p>Employee ID: {{ $user->employee_id }}</p>
    <!-- Include other relevant user details -->

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
                <td>{{ $user->basic_pay }}</td>
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
                <td>{{ $user->paye }}</td>
            </tr>
            <!-- Add other deduction components -->
        </tbody>
    </table>

    <p>Total Earnings: {{ $user->total_benefits }}</p>
    <p>Total Deductions: {{ $user->total_other_deduction }}</p>
    <p>Net Salary: {{ $user->netpay }}</p>
</body>
</html>
