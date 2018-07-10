const schoolunits = document.getElementById('schoolunits');

if (schoolunits) {
  schoolunits.addEventListener('click', (e) => {
    if(e.target.className === 'btn btn-danger delete-unit') {
      if(confirm('Esti sigur că vrei să ștergi?')) {
        const id = e.target.getAttribute('data-id');
        //alert(`This is id :: ${id}`);
        fetch(`/school/unit/delete/${id}`, {
          //credentials tag is required to avoid redirect to login page
          //credentials: 'omit',
          credentials: 'same-origin',
          //credentials: 'include',
          cache: 'no-cache',
          method: 'DELETE'
        }).then(res => window.location.reload());
        console.log(`Deleted id :: ${id}`);
      }
    }
  });
}
