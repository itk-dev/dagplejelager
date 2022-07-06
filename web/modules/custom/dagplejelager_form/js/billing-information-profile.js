; (function ($) {
  $(function () {
    $('#edit-billing-information-profile-dagplejelager-form')
      .autocomplete({
        select: (event, ui) => {
          if (ui.item.name) {
            for (const [key, value] of Object.entries(ui.item.name)) {
              $('[name="billing_information[profile][address][0][address]['+key+']"]').val(value)
            }
          }
          if (ui.item.address) {
            for (const [key, value] of Object.entries(ui.item.address)) {
              $('[name="billing_information[profile][address][0][address]['+key+']"]').val(value)
            }
          }
          if (ui.item.institution) {
            for (const [key, value] of Object.entries(ui.item.institution)) {
              $('[name="billing_information[profile][field_institution_'+key+'][0][value]"]').val(value)
            }
          }
          if (ui.item.telephone_number) {
            $('[name="billing_information[profile][field_telephone_number][0][value]"]').val(ui.item.telephone_number)
          }
        }
      });
  })
}(jQuery))
