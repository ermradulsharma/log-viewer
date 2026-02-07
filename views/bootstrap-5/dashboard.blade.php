@extends('log-viewer::bootstrap-5._master')

@section('content')
    <div class="row justify-content-center py-3">
        <div class="col-4">
            <div class="card m-0">
                <div class="card-header">
                    <h5 class="card-title m-0">@lang('Log Statistics')</h5>
                </div>
                <div class="card-body">
                    <canvas id="stats-doughnut-chart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-8">
            <div class="row">
                @foreach ($percents as $level => $item)
                    <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                        <div class="box level-{{ $level }} {{ $item['count'] === 0 ? 'empty' : '' }} h-100">
                            <div class="box-icon">{!! log_styler()->icon($level) !!}</div>
                            <div class="box-content">
                                <span class="box-text">{{ $item['name'] }}</span>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="box-number">{{ $item['count'] }}</span>
                                    @if ($item['count'] > 0)
                                        <small class="text-muted">{{ $item['percent'] }}%</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        ready(function() {
            new Chart(document.getElementById("stats-doughnut-chart"), {
                type: 'doughnut',
                data: {!! $chartData !!},
                options: {
                    legend: {
                        position: 'bottom'
                    }
                }
            });
        });
    </script>
@endsection
