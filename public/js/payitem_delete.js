const paymentitems = document.getElementById('paymentitems');

if (paymentitems) {
  paymentitems.addEventListener('click', (e) => {
    if(e.target.className === 'btn btn-danger delete-account-item') {
      if(confirm('Esti sigur că vrei să ștergi acest produs?\r\nVa fii șters din orice factură care nu a fost emisă...')) {
        const itemId = e.target.getAttribute('data-id');
        //alert(`This is id :: ${id}`);
        fetch(`/accounts/item/${itemId}/delete`, {
          //credentials tag is required to avoid redirect to login page
          //credentials: 'omit',
          credentials: 'same-origin',
          //credentials: 'include',
          cache: 'no-cache',
          method: 'DELETE'
        }).then(res => window.location.reload());
        console.log(`Deleted id :: ${itemId}`);
      }
    }
  });
}
