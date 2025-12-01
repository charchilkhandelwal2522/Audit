
<div class="chart-card">
    <h4>@lang('audit::app.performanceOverview')</h4>
    <div class="chart-container-large">
        <canvas id="performanceOverviewChart"></canvas>
    </div>
</div>

<script>

    // Chart colors
    const chartColors = {
            red: '#ef4444',
            orange: '#f97316',
            yellow: '#eab308',
            teal: '#14b8a6',
            green: '#22c55e',
            blue: '#3b82f6'
        };

    // Performance Overview Chart (Combined Bar + Line)
    const performanceOverviewCtx = document.getElementById('performanceOverviewChart').getContext('2d');
    const monthlyLabels = {!! json_encode(array_keys($monthlyData)) !!};
    const monthlyAuditCounts = {!! json_encode(array_column($monthlyData, 'count')) !!};
    const monthlyAvgScores = {!! json_encode(array_column($monthlyData, 'avgScore')) !!};

    new Chart(performanceOverviewCtx, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [
                {
                    type: 'line',
                    label: '@lang("audit::app.averageScore")',
                    data: monthlyAvgScores,
                    borderColor: chartColors.green,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0.4,
                    yAxisID: 'y',
                    pointBackgroundColor: chartColors.green,
                    pointRadius: 4
                },
                {
                    type: 'bar',
                    label: '@lang("audit::app.totalAudits")',
                    data: monthlyAuditCounts,
                    backgroundColor: chartColors.blue,
                    borderRadius: 4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'center'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    min: 0,
                    max: 100,
                    ticks: {
                        stepSize: 20,
                        color: '#64748b'
                    },
                    grid: {
                        color: '#e2e8f0',
                        drawBorder: false
                    },
                    title: { display: false }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    min: 0,
                    grid: { display: false },
                    title: { display: false }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>
