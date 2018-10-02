const guardians = document.getElementById('users');

if (guardians) {
  guardians.addEventListener('click', (e) => {
    if(e.target.className === 'far fa-envelope reset-user') {
      e.preventDefault();
      if(confirm('Ești sigur că vrei să trimiți un e-mail cu resetarea parolei acestui utilizator?\n(Parola veche va fii suprascrisă)')) {
        const id = e.target.getAttribute('data-id');
        //alert(`This is id :: ${id}`);
        fetch(`/user/reset/${id}`, {
          //credentials tag is required to avoid redirect to login page
          credentials: 'same-origin',
          method: 'POST'
        }).then(res => { alert('Contul a fost resetat cu succes!'); } );
        //alert('Contul a fost resetat cu succes!');
        //console.log(`RESET e-mail sent to id :: ${id}`);
      }
    }
  });
}
