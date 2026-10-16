@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('images/covadis-logo.png') }}" class="logo" alt="Covadis">
<span class="brand">{!! $slot !!}</span>
</a>
</td>
</tr>
