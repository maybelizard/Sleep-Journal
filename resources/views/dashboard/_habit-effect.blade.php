<p class="font-medium text-gray-800">{{ $h['name'] }}</p>
<p class="text-xs text-gray-500">
    Quality {{ number_format($h['quality_with'], 1) }} vs {{ number_format($h['quality_without'], 1) }}
    ({{ sprintf('%+.1f', $h['quality_diff']) }})
    · Sleep {{ sprintf('%+d', round($h['duration_diff'])) }} min
    · {{ $h['n_with'] }} vs {{ $h['n_without'] }} nights
</p>