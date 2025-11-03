/* global $, $document */

$(function () {
  const table = $('.datatables-vatMethods');
  if (table.length) {
    const dt = table.DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: baseUrl + 'settings/vat-method/list/ajax',
        dataSrc: 'data'
      },
      columns: [
        { data: 'name' },
        { data: null }, // VAT formatted
        { data: 'status' },
        { data: null }
      ],
      columnDefs: [
        {
          targets: 1,
          render: function (data, type, row) {
            if (row.type === 'Percentage') {
              return `${parseFloat(row.amount).toFixed(2)}%`;
            }
            const symbol = window.currencySymbol || '₱';
            return `${symbol}${parseFloat(row.amount).toFixed(2)}`;
          }
        },
        {
          targets: 2,
          render: function (data) {
            return data === 'Active' ? '<span class="badge bg-label-success">Active</span>' : '<span class="badge bg-label-danger">Inactive</span>';
          }
        },
        {
          targets: 3,
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            return (
              '<button class="btn btn-text-secondary rounded-pill waves-effect btn-edit-vat" data-id="' + row.id + '">' +
              '<i class="ti tabler-edit"></i></button>'
            );
          }
        }
      ]
    });

    // Edit handler: load and show modal
    table.on('click', '.btn-edit-vat', function () {
      const id = $(this).data('id');
      $.get('/settings/vat-method/ajax/show', { id }, function (res) {
        if (res) {
          const modal = $('#ajaxEditVATMethodModal');
          modal.find('input#id').val(res.id);
          modal.find('input#vatName').val(res.name);
          modal.find('select#vatType').val(res.type);
          modal.find('input#vatAmount').val(res.amount);
          modal.find('select#vatStatus').val(res.status);
          modal.modal('show');
        }
      });
    });
  }
});


