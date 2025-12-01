<div class="chart-card">
    <h4>@lang('audit::app.scoreDistribution')</h4>
    <div class="chart-container">
        <canvas id="scoreDistributionChart"></canvas>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Chart colors (shared)
        if (typeof window.chartColors === 'undefined') {
            window.chartColors = {
                red: '#ef4444',
                orange: '#f97316',
                yellow: '#eab308',
                teal: '#14b8a6',
                green: '#22c55e',
                blue: '#3b82f6'
            };
        }

        // Score Distribution Chart
        const scoreDistributionCtx = document.getElementById('scoreDistributionChart').getContext('2d');
        new Chart(scoreDistributionCtx, {
            type: 'bar',
            data: {
                labels: ['0-20%', '21-40%', '41-60%', '61-80%', '81-100%'],
                datasets: [{
                    data: [
                        {{ $scoreDistribution['0-20'] }},
                        {{ $scoreDistribution['21-40'] }},
                        {{ $scoreDistribution['41-60'] }},
                        {{ $scoreDistribution['61-80'] }},
                        {{ $scoreDistribution['81-100'] }}
                    ],
                    backgroundColor: [
                        window.chartColors.red,
                        window.chartColors.orange,
                        window.chartColors.yellow,
                        window.chartColors.teal,
                        window.chartColors.green
                    ],
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
