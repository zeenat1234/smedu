
  function ShowHideFunction() {
    if ($('#smartGenStuds').height() == "0") {
      document.getElementById('smartGenStuds').style.maxHeight = "100%";
    } else {
      document.getElementById('smartGenStuds').style.maxHeight = "0";
    }
  }

$(document).ready(function () {
  //submit form
	$(document).on('click', '#submitBtn', function(event){
		event.preventDefault();
		var currForm = $(this).closest('form');
		var data = currForm.serialize();

		$.ajax({
			url: process_form_url,
			method: "POST",
			data: data,
			success: function(response){
				$('#content').html($(response.view).find('#content').html());
        $('#smart_generate_students').select2({
          placeholder: 'Căutați numele elevilor...',
        });

        if($('input[name="smart_generate[stud_choice]"').val() == 'all'){
          $('#smartGenStuds').toggle();
        }
			}
		});
	});

  $(document).on('click', 'input[name="smart_generate[stud_choice]"]', function(){
      if($(this).val() == 'all'){
          $('#smartGenStuds').toggle(800);
      } else {
          $('#smartGenStuds').toggle(800);
      }
  });

  $('#smart_generate_students').select2({
    placeholder: 'Căutați numele elevilor...',
  });

  var studChoice = $('input[name="smart_generate[stud_choice]"').val();
  if(studChoice == 'all'){
    $('#smartGenStuds').toggle();
  }

  $("#submitBtn").click(function () {
    setTimeout(function () {
      $("#submitBtn").prop('disabled', true);
      $("#icon").addClass('fa-spin');
    }, 0);
  });

    $('#smart_generate_pay_item_type_1').change(function(){
      if ($('#smart_generate_pay_item_type_1').prop('checked') || $('#smart_generate_pay_item_type_2').prop('checked')) {
        document.getElementById('smartGenOptDates').style.maxHeight = "200px";
      } else {
        document.getElementById('smartGenOptDates').style.maxHeight = "0";
      }
    });

    $('#smart_generate_pay_item_type_2').change(function(){
      if ($('#smart_generate_pay_item_type_1').prop('checked') || $('#smart_generate_pay_item_type_2').prop('checked')) {
        document.getElementById('smartGenOptDates').style.maxHeight = "200px";
      } else {
        document.getElementById('smartGenOptDates').style.maxHeight = "0";
      }
    });

    $('#autoInvoice').change(function(){
      if ($('#smart_generate_auto_invoice_0').prop('checked')) {
        document.getElementById('smartGenInvoicing').style.maxHeight = "0";
        document.getElementById('smartGenInvDate').style.maxHeight = "0";
      } else {
        document.getElementById('smartGenInvoicing').style.maxHeight = "200px";
        document.getElementById('smartGenInvDate').style.maxHeight = "200px";
      }
    });

    if ($('#smart_generate_pay_item_type_1').prop('checked') || $('#smart_generate_pay_item_type_2').prop('checked')) {
      document.getElementById('smartGenOptDates').style.maxHeight = "200px";
    } else {
      document.getElementById('smartGenOptDates').style.maxHeight = "0";
    }

    if ($('#smart_generate_auto_invoice_0').prop('checked')) {
      document.getElementById('smartGenInvoicing').style.maxHeight = "0";
      document.getElementById('smartGenInvDate').style.maxHeight = "0";
    } else {
      document.getElementById('smartGenInvoicing').style.maxHeight = "200px";
      document.getElementById('smartGenInvDate').style.maxHeight = "200px";
    }

  });