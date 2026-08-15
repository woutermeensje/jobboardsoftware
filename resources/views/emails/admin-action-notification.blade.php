@php
  $adminUrl = route('admin.dashboard');

  $formatValue = function (mixed $value) use (&$formatValue): string {
      if (is_bool($value)) {
          return $value ? 'Yes' : 'No';
      }

      if ($value instanceof \DateTimeInterface) {
          return $value->format('Y-m-d H:i:s');
      }

      if (is_array($value)) {
          return collect($value)
              ->map(fn (mixed $item, string|int $key): string => is_int($key)
                  ? $formatValue($item)
                  : $key.': '.$formatValue($item))
              ->implode(', ');
      }

      return (string) $value;
  };
@endphp

<h1>{{ $title }}</h1>

@if($actor)
  <p>
    <strong>User:</strong>
    {{ $actor['name'] ?? 'Unknown' }}
    @if(! empty($actor['email']))
      &lt;{{ $actor['email'] }}&gt;
    @endif
    @if(! empty($actor['role']))
      ({{ $actor['role'] }})
    @endif
  </p>
@endif

@if($details)
  <table cellpadding="8" cellspacing="0" border="0" style="border-collapse: collapse;">
    @foreach($details as $label => $value)
      <tr>
        <th align="left" style="border-bottom: 1px solid #e5e7eb;">{{ ucfirst(str_replace('_', ' ', (string) $label)) }}</th>
        <td style="border-bottom: 1px solid #e5e7eb;">{{ $formatValue($value) }}</td>
      </tr>
    @endforeach
  </table>
@endif

<p>
  <a href="{{ $adminUrl }}">Open admin dashboard</a>
</p>
