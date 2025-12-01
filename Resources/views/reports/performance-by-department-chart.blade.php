<div class="chart-card">
    <h4>@lang('audit::app.performanceByDepartment')</h4>
    <div class="chart-container">
        <canvas id="departmentPerformanceChart"></canvas>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        const chartColors = {
            blue: '#3b82f6'
        };

        const deptPerformanceCtx = document.getElementById('departmentPerformanceChart').getContext('2d');
        new Chart(deptPerformanceCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($departmentPerformance ?? [])) !!},
                datasets: [{
                    data: {!! json_encode(array_values($departmentPerformance ?? [])) !!},
                    backgroundColor: chartColors.blue,
                    borderRadius: 4,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endpush
