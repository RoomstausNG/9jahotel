<div class="px-2 py-4 mx-auto space-y-3 bg-white border min-w-[850px] max-w-[1024px] print:block">
    <div class="w-full">
        <h1 class="text-xl font-medium text-center">Staff Payroll </h1>
    </div>
    <table class="w-full border">
        <thead>
            <tr class="border">
                <th class="p-4 text-left">Staff Name</th>
                <th class="p-4 text-left">Salary</th>
                <th class="p-4 text-left">Allowances</th>
                <th class="p-4 text-left">Gross Salary</th>
                <th class="p-4 text-left">Deductions</th>
                <th class="p-4 text-left">Net Payment</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @foreach ($staff as $staff)
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
                <tr>
                    <td class="px-4 py-3 capitalize border">{{ $staff->firstname . ' ' . $staff->lastname }}</td>
                    <td class="px-4 py-3 border">{{ formatAsNaira($staff->gross_annual_salary) }}</td>
                    <td class="px-4 py-3 border">{{ formatAsNaira($allowances) }}</td>
                    <td class="px-4 py-3 text-center border">
                        {{ formatAsNaira($gross_salary) }}</td>
                    <td class="px-4 py-3 border">{{ formatAsNaira($deductions) }}</td>
                    <td class="px-4 py-3 text-center border">
                        {{ formatAsNaira($gross_salary - $deductions) }}</td>

                    <td class="border">
                        <p class="flex items-center justify-center px-2 py-1 space-x-2 text-sm">

                            <a href="{{ route('salary', $staff->username) }}" data-tooltip-placement="left"
                                class="flex items-center justify-center px-2 py-1 space-x-2 text-green-500 border border-green-500 rounded-md">
                                <i class="mr-1 fa-solid fa-money-bill-1"></i> Print
                            </a>

                            <a href="{{ route('users.profile', $staff->username) }}"
                                wire:click="editUser({{ $staff->id }})" data-tooltip-target="edit"
                                data-tooltip-placement="left"
                                class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
                                <i class="mr-1 fa-solid fa-mail"></i> Email
                            </a>
                        </p>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <button onclick="window.print()"
        class="inline-block px-2 py-1 mr-2 text-sm text-white bg-blue-600 rounded cursor-pointer max-w-max print:hidden">
        <i class="fa-solid fa-print"></i>
        print</button>
    <a href="{{ route('report') }}"
        class="inline-block px-2 py-1 mr-2 text-sm text-white bg-gray-600 rounded cursor-pointer max-w-max print:hidden">
        Back</a>
</div>
