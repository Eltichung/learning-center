// Demo: chọn trạng thái điểm danh (UI only, chưa lưu)
document.querySelectorAll('.seg span').forEach(function(s){
  s.addEventListener('click', function(){
    this.parentElement.querySelectorAll('span').forEach(function(x){ x.classList.remove('on'); });
    this.classList.add('on');
  });
});
