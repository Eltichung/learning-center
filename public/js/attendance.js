// Điểm danh: chọn trạng thái -> set hidden input + tính lại thành tiền/tổng
function attRecalc(){
  var total = 0, cnt = 0;
  document.querySelectorAll('#att-table tbody tr[data-price]').forEach(function(tr){
    var price = parseInt(tr.dataset.price || '0', 10);
    var h = tr.querySelector('input[type=hidden]');
    var val = h ? h.value : 'present';
    // Có mặt / học bù / vắng không phép đều tính tiền; chỉ vắng có phép (excused) miễn
    var pay = (val === 'present' || val === 'makeup' || val === 'absent') ? price : 0;
    var cell = tr.querySelector('.thanhtien');
    if (cell){
      cell.textContent = pay.toLocaleString('vi-VN') + 'đ';
      cell.style.color = pay ? '' : 'var(--muted)';
    }
    if (pay) { cnt++; total += pay; }
  });
  var t = document.getElementById('att-total');
  if (t) t.textContent = cnt + ' buổi · ' + total.toLocaleString('vi-VN') + 'đ';
}

document.addEventListener('click', function(e){
  var sp = e.target.closest('.seg span');
  if (!sp || !sp.dataset.val) return;
  var seg = sp.parentElement;
  seg.querySelectorAll('span').forEach(function(x){ x.classList.remove('on'); });
  sp.classList.add('on');
  var tr = sp.closest('tr');
  var h = tr ? tr.querySelector('input[type=hidden]') : null;
  if (h) h.value = sp.dataset.val;
  attRecalc();
});

document.addEventListener('DOMContentLoaded', attRecalc);
