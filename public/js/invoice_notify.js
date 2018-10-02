const invoices = document.getElementById('invoices');

if (invoices) {
  invoices.addEventListener('click', (e) => {
    if(e.target.className === 'notify_button') {
      if(confirm('Ești sigur că vrei să trimiți notificare de plată prin e-mail?')) {
        const invId = e.target.getAttribute('data-id');
        //alert(`This is id :: ${id}`);
        fetch(`/accounts/invoice_notify/${invId}`, {
          //credentials tag is required to avoid redirect to login page
          credentials: 'same-origin',
          method: 'POST'
        }).then(res => { alert('Notificarea a fost trimisă cu succes!'); window.location.reload(); } );
        //alert('Contul a fost resetat cu succes!');
        //console.log(`RESET e-mail sent to id :: ${id}`);
      }
    }
    //TODO: Consider implementing ajax for PDFs
    // else if(e.target.className === 'xxxxpdf_button') {
    //     if(confirm('Ești sigur că PDF...?\n(Încă o explicatie)')) {
    //       const invId = e.target.getAttribute('data-id');
    //       //alert(`This is id :: ${id}`);
    //       fetch(`/accounts/invoice_pdf/${invId}`, {
    //         //credentials tag is required to avoid redirect to login page
    //         credentials: 'same-origin',
    //         method: 'GET'
    //       });
    //       //alert('Contul a fost resetat cu succes!');
    //       //console.log(`RESET e-mail sent to id :: ${id}`);
    //     }
    //   }
  });
}
