<?php

namespace App\Http\Controllers;

use App\Http\Requests\WithdrawRequest;
use App\Http\Services\MoneyService;
use App\Models\BankModel;
use App\Models\OtpData;
use App\Models\TraceSystem;
use App\Models\User;
use App\Models\WithdrawModel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MoneyController extends Controller
{
    public function recharge(): Factory|View|Application
    {
        session()->flash('menu-active', 'menu-recharge');
        return view('money.recharge');
    }

    public function withdraw(): Factory|View|Application
    {
        $banks = BankModel::whereUserId(user()->id)->get();
        session()->flash('menu-active', 'menu-withdraw');
        return view('money.withdraw', compact('banks'));
    }

    public function withdrawPost(WithdrawRequest $request): RedirectResponse
    {
        if(user()->security_level_2 === 1){
            if(empty($request->otp_hash) || empty($request->otp_code)){
                session()->flash('mgs_error', 'Bạn chưa nhập mã OTP!');
                return redirect()->back();
            }
            if(!OtpData::verify($request->otp_hash, $request->otp_code)){
                session()->flash('mgs_error', 'Mã OTP không khớp!');
                return redirect()->back()->withInput();
            }
        }

        $user = User::whereId(user()->id)->first();
        if((int)$request->money < 100000) {
            session()->flash('mgs_error', 'Số tiền rút ít nhất là 100.000đ!');
            return redirect()->back();
        }

        if((int)$request->money > (int)$user->money) {
            session()->flash('mgs_error', 'Số tiền bạn muốn rút lớn hơn số tiền có trong tài khoản. Vui lòng kiểm tra lại số dư và tạo lại yêu cầu mới!');
            return redirect()->back();
        }

        $statusWithdraw = MoneyService::withdraw($request);
        $mgs = $statusWithdraw ? 'Tạo yêu cầu rút tiền thành công!' : 'Tạo yêu cầu rút tiền thất bại. Hãy liên hệ admin để giải quyết!';

        if($statusWithdraw){
            TraceSystem::setTrace([
                'mgs' => 'User tạo yêu cầu rút tiền',
                ...$request->validated()
            ]);
        }

        session()->flash('notif', $mgs);
        return redirect()->to('/');
    }

    public function withdrawHistory (): Factory|View|Application
    {
        $histories = WithdrawModel::with('bank_relation')->whereUserId(user()->id)->orderBy('created_at', 'DESC')->get();
        return view('money.withdraw_history', compact('histories'));
    }

    public function plusMoneyUser(Request $request): JsonResponse
    {
        $username = $request->username;
        $money_plus = $request->money_plus;

        $user = User::whereUsername($username)->first();
        if($user == null){
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản người dùng không tồn tại!',
                'data' => []
            ]);
        }

        if(!preg_match('/^(([1-9]\d*)|(-[1-9]\d*))$/i', $money_plus)){
            return response()->json([
                'success' => false,
                'message' => 'DM nhập cái số tiền ngu vc!',
                'data' => []
            ]);
        }
        $money_plus = explode('-', $money_plus);
        if(count($money_plus) == 2) {
            TraceSystem::setTrace([
                'mgs' => 'Admin trừ tiền của user',
                'username' => $user->username,
                'money_minus' => (int)$money_plus[1],
                'money_before' => (int)$user->money,
                'money_after' => (int)$user->money - (int)$money_plus[1]
            ]);
            $user->money = (int)$user->money - (int)$money_plus[1];
        }else{
            TraceSystem::setTrace([
                'mgs' => 'Admin cộng tiền cho user',
                'username' => $user->username,
                'money_minus' => (int)$money_plus[0],
                'money_before' => (int)$user->money,
                'money_after' => (int)$user->money + (int)$money_plus[0]
            ]);
            $user->money = (int)$user->money + (int)$money_plus[0];
        }

        $user->save();
        return response()->json([
            'success' => true,
            'message' => "Thay đổi số tiền thành công",
            'data' => [
                'money' => number_format($user->money)
            ]
        ]);
    }
}
