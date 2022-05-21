<table class="table table-hover table-responsive text-center custom-scrollbar">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Trạng thái</th>
        <th scope="col">Ngày tạo</th>
        <th scope="col">Ngân hàng</th>
        <th scope="col">Số tiền</th>
        <th scope="col">Ghi chú</th>
    </tr>
    </thead>
    <tbody>
    @php($stt = 1)
    @foreach($histories as $history)
        @php($bank = $history->bank_relation)
        <tr>
            <th scope="row">{{ $stt++ }}</th>
            <td style="min-width: 150px;">{!! $history->getStatus() !!}</td>
            <td style="min-width: 130px;">{{ date('d/m/Y', strtotime($history->created_at)) }}</td>
            <td style="min-width: 250px">{{ $bank->account_number }} ({{ getNameBank($bank->type, $bank->name) }} - {{ $bank->account_name }})</td>
            <td style="min-width: 180px;">{{ number_format($history->money) }} VNĐ</td>
            <td style="min-width: 200px;">{{ $history->note }}</td>
        </tr>
    @endforeach
    @if($histories->count() == 0)
        <tr>
            <td colspan="6" style="min-width: 615px;">Không có lịch sử</td>
        </tr>
    @endif
    </tbody>
</table>
