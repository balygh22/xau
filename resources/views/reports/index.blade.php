@extends('layouts.app')
@section('title', 'التقارير')
@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0"><i class="fas fa-chart-line" style="margin-left:8px"></i> التقارير</h1>
</div>
<div style="margin-top:16px;background:#fff;border-radius:12px;border:1px solid #e5e7eb;padding:16px">
    <ul style="margin:0;padding:0;list-style:none">
        <li style="margin-bottom:8px"><a href="{{ route('reports.sales') }}">تقرير المبيعات (ذهب)</a></li>
        <li><a href="{{ route('reports.inventory') }}">تقرير المخزون (أوزان)</a></li>
    </ul>
</div>
@endsection