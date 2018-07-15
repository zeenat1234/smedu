const nextyears = document.getElementById('nextyears');

if (nextyears) {
  nextyears.addEventListener('click', (e) => {
    if(e.target.className === 'btn btn-danger delete-year') {
      if(confirm('Esti sigur că vrei să ștergi anul?')) {
        const id = e.target.getAttribute('data-id');
        //alert(`This is id :: ${id}`);
        fetch(`/school/year/delete/${id}`, {
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
