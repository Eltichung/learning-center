<div class="modal-backdrop" id="m-pay">
  <form class="modal" method="POST" action="{{ route('teacher.payments.store', [], false) }}">
    @csrf
    <div class="mh"><h3>Ghi nhận đóng tiền</h3><button type="button" class="x" onclick="closeModal(this)">&times;</button></div>
    <div class="mb">
      <div class="field"><label>Học sinh <span style="color:var(--red)">*</span></label>
        <div class="ssel" data-url="{{ route('api.students.search') }}" id="pay-ssel">
          <input type="hidden" name="student_id" id="pay-student-id">
          <input class="ssel-input" id="pay-student-input" placeholder="Gõ tên / mã học sinh..." autocomplete="off">
          <div class="ssel-list"></div>
        </div>
      </div>
      <div class="due-card" id="pay-balance-box" style="display:none;margin-bottom:14px">
        <div class="due-total">Công nợ hiện tại</div>
        <div class="amt" id="pay-balance-amt">—</div>
      </div>
      <div class="grid2">
        <div class="field"><label>Số tiền nhận (VNĐ) <span style="color:var(--red)">*</span></label>
          <input class="money-input" data-target="amount" inputmode="numeric" placeholder="0" required>
          <input type="hidden" name="amount" id="pay-amount" value="">
        </div>
        <div class="field"><label>Hình thức <span style="color:var(--red)">*</span></label>
          <select name="method"><option value="transfer">Chuyển khoản</option><option value="cash">Tiền mặt</option></select></div>
      </div>
      <div class="grid2">
        <div class="field"><label>Ngày đóng <span style="color:var(--red)">*</span></label><input type="date" name="paid_at" value="{{ now()->toDateString() }}" required></div>
        <div class="field"><label>Ghi chú</label><input name="note" placeholder="VD: Học phí tháng {{ now()->month }}"></div>
      </div>
    </div>
    <div class="mf"><button type="button" class="btn ghost" onclick="closeModal(this)">Huỷ</button><button type="submit" class="btn primary">Ghi nhận đóng tiền</button></div>
  </form>
</div>

<script>
(function(){
  function setAmount(v){
    var d=document.querySelector('#m-pay .money-input'), h=document.getElementById('pay-amount');
    h.value=String(v||'').replace(/\D/g,''); if(d) d.value=h.value?window.fmtMoney(h.value):'';
  }
  function showBalance(bal){
    document.getElementById('pay-balance-amt').textContent = bal>0 ? bal.toLocaleString('vi-VN')+'đ' : 'Đã đóng đủ';
    document.getElementById('pay-balance-box').style.display='';
  }
  // Mở popup cho 1 học sinh cụ thể (đã biết công nợ)
  window.payFor = function(id, name, balance){
    document.getElementById('pay-student-id').value=id;
    document.getElementById('pay-student-input').value=name;
    showBalance(balance||0);
    setAmount(balance>0?balance:'');   // gợi ý đóng đủ công nợ
    openModal('m-pay');
  };
  // Mở popup chọn học sinh từ đầu
  window.payNew = function(){
    document.getElementById('pay-student-id').value='';
    document.getElementById('pay-student-input').value='';
    document.getElementById('pay-balance-box').style.display='none';
    setAmount('');
    openModal('m-pay');
  };
  document.addEventListener('DOMContentLoaded', function(){
    var ssel=document.getElementById('pay-ssel');
    if(ssel){ ssel.addEventListener('ssel:select', function(e){
      fetch('{{ url('/api/students') }}/'+e.detail.id+'/monthly', {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){return r.json();})
        .then(function(d){ showBalance(d.balance); setAmount(d.balance>0?d.balance:''); })
        .catch(function(){});
    }); }
  });
})();
</script>
