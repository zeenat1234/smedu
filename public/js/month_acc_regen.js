const monthaccount = document.getElementById('monthaccount');

if (monthaccount) {
  monthaccount.addEventListener('click', (e) => {
    if(e.target.className === 'regenerate-account') {
      if(confirm('Esti sigur că vrei să regenerezi contul?\r\nToate Modificările ȘI Facturile vor fii șterse!\r\nClick OK pentru a continua...')) {
        const accId = e.target.getAttribute('data-id');
        //alert(`This is id :: ${id}`);
        fetch(`/accounts/${accId}/reset`, {
          //credentials tag is required to avoid redirect to login page
          //credentials: 'omit',
          credentials: 'same-origin',
          //credentials: 'include',
          cache: 'no-cache',
          method: 'DELETE'
        }).then(res => window.location.reload());
        console.log(`Deleted id :: ${accId}`);
      }
    }
  });
}
