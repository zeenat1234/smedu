const schoolservices = document.querySelectorAll('[id^="schoolservices-"]');
console.log(schoolservices);
for (var i=0; i < schoolservices.length; i++) {

  if (schoolservices[i]) {
    schoolservices[i].addEventListener('click', (e) => {
      //console.log('am dat click!');
      if(e.target.className === 'btn btn-danger delete-service') {
        if(confirm('Esti sigur că vrei să ștergi?')) {
          const id = e.target.getAttribute('data-id');
          //alert(`This is id :: ${id}`);
          fetch(`/school/service/delete/${id}`, {
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
}
