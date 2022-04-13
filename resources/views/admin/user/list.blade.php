@extends('admin_layout')
@section('contents')
    <div class="content">
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    {{ $action == 'active' ? 'Danh sách thành viên hoạt động!' : 'Danh sách thành viên đã bị chặn!' }}
                </h3>
            </div>
            <div class="block-content">
                <div class="table-responsive">
                    <table class="table table-striped table-vcenter text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tài khoản</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Số tiền</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($stt = 1)
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $stt++ }}</td>
                                    <td class="font-w600">{{ $user->username }}</td>
                                    <td>{{ $user->fullname }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td class="row-money">{{ number_format($user->money) }}</td>
                                    <td>{{ date('d/m/Y', strtotime($user->created_at)) }}</td>
                                    <td style="min-width: {{ $action == 'active' ? '200px' : '130px' }}">
                                        @if($action == 'active')
                                            <a href="#" data-username="{{ $user->username }}" class="btn btn-primary btn-plus-money">Cộng tiền</a>
                                            <a href="{{ route('admin.user.change-active', ['id' => $user->id, 'status' => (int)!$user->inactive]) }}" class="btn btn-danger" onclick="return confirm('Chắc chắn chặn người này?')">Chặn</a>
                                        @else
                                            <a href="{{ route('admin.user.change-active', ['id' => $user->id, 'status' => (int)!$user->inactive]) }}" class="btn btn-primary" onclick="return confirm('Chắc chắn mở chặn người này?')">Mở</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if($users->count() <= 0)
                                <tr>
                                    <td colspan="7">Không có người dùng nào {{ $action == 'active' ? 'đang hoạt động!' : 'bị chặn!' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-change-money" tabindex="-1" role="dialog" aria-labelledby="modal-popin" aria-hidden="true">
        <div class="modal-dialog modal-dialog-popin" role="document">
            <div class="modal-content">
                <div class="block block-themed block-transparent mb-0">
                    <div class="block-header bg-primary-dark">
                        <h3 class="block-title">Cộng tiền cho <b class="username"></b></h3>
                        <div class="block-options">
                            <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                                <i class="si si-close"></i>
                            </button>
                        </div>
                    </div>
                    <div class="block-content">
                        <form action="" method="POST" id="form-plus-money-user">
                            <input type="hidden" name="username" value="">
                            <div class="alert alert-info">Nếu muốn trừ tiền, thêm dấu - trước số tiền. Ví dụ: -10000</div>
                            <div class="form-group">
                                <label for="money_plus">Số tiền</label>
                                <input type="text" class="form-control" id="money_plus" name="money_plus" placeholder="Số tiền cộng">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-alt-secondary" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-alt-success btn-submit-plus-money">
                        <i class="fa fa-check"></i> Đồng ý
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        let href = '{{ route('plus-money') }}';
        let model = $('#modal-change-money');
        let _form = $('#form-plus-money-user');
        $('.btn-plus-money').on('click', function(e){
            e.preventDefault();
            let username = $(this).attr('data-username');
            model.find('.username').text(username);
            model.find('[name="username"]').attr('value', username);
            model.modal('show');
        });
        $('.btn-submit-plus-money').on('click', function(e){
            e.preventDefault();
            let formData = new FormData(_form[0]);
            Request.ajax(href, formData, function(result){
                model.modal('hide');
                if(!result.success) {
                    alertify.alert('Error', result.message)
                    $('.alertify .ajs-header').addClass('alert-danger');
                    return false;
                }

                alertify.alert('Success', result.message)
                $('.alertify .ajs-header').addClass('alert-success');

                let newMoney = result.data.money;
                let username = model.find('[name="username"]').val();
                $('[data-username="' + username + '"]').closest('tr').find('.row-money').text(newMoney);
                return false;
            });
        })
    </script>
@endsection