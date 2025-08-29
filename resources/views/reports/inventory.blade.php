@extends('layouts.app')
@section('title', 'تقرير المخزون')
@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0"><i class="fas fa-warehouse" style="margin-left:8px"></i> تقرير المخزون</h1>
</div>

<div class="table-responsive mt-3">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>الصنف</th>
                <th>الإجمالي (عدد)</th>
                <th>الإجمالي (وزن)</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td>{{ $r->category }}</td>
                <td>{{ number_format($r->units) }}</td>
                <td>
                    @if(property_exists($r,'weight'))
                        {{ number_format($r->weight, 3) }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center">لا توجد بيانات</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection