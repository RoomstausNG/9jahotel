<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $staff->username }}</title>
    @vite(['resources/css/app.css', 'resources/scss/main.scss'])
</head>

<body class="flex flex-col justify-center pt-8 bg-gray-100 print:bg-none">
    <div class="px-2 py-4 mx-auto space-y-3 bg-white border min-w-[750px] max-w-[900px] print:block">
        <table class="w-full border">
            <thead>
                <tr class="border">
                    <th class="p-4 text-left">Staff Name</th>
                    <th class="p-4 text-left">Salary</th>
                    <th class="p-4 text-left">Allowances</th>
                    <th class="p-4 text-left">Gross Salary</th>
                    <th class="p-4 text-left">Deductions</th>
                    <th class="p-4 text-left">Net Payment</th>
                </tr>
            </thead>
            @php
                $deductions = $staff->other_deductions + $staff->surcharges + $staff->payee;
                $allowances =
                    $staff->special_bonus +
                    $staff->health_insurance +
                    $staff->transport_insurance +
                    $staff->housing_insurance +
                    $staff->overtime_insurance +
                    $staff->special_duty_insurance;

                $gross_salary = $allowances + $staff->gross_annual_salary;
            @endphp
            <tbody>
                <tr>
                    <th class="px-4 py-3 capitalize border">{{ $staff->firstname . ' ' . $staff->lastname }}</th>
                    <th class="px-4 py-3 border">{{ formatAsNaira($staff->gross_annual_salary) }}</th>
                    <th class="px-4 py-3 border">{{ formatAsNaira($allowances) }}</th>

                    <th class="px-4 py-3 border">
                        {{ formatAsNaira($gross_salary) }}</th>
                    <th class="px-4 py-3 border">{{ formatAsNaira($deductions) }}</th>
                    <th class="px-4 py-3 border">
                        {{ formatAsNaira($gross_salary - $deductions) }}</th>
                </tr>
            </tbody>
        </table>
        <button onclick="window.print()"
            class="inline-block px-2 py-1 mr-2 text-sm text-white bg-blue-600 rounded cursor-pointer max-w-max print:hidden">
            <i class="fa-solid fa-print"></i>
            print</button>
        <a href="{{ route('configuration.users') }}"
            class="inline-block px-2 py-1 mr-2 text-sm text-white bg-gray-600 rounded cursor-pointer max-w-max print:hidden">
            Back</a>
    </div>

</body>

</html>
