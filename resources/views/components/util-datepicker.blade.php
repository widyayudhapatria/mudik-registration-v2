{{-- Datepicker Utilities untuk Registration Form --}}
@push('scripts')
<script>
  /**
   * Initialize datepicker untuk input tanggal
   * @param {string} selector - jQuery selector untuk input element
   */
  function initializeDatepicker(selector) {
    $(selector).datepicker({
      uiLibrary: "bootstrap5",
      format: "dd/mm/yyyy",
      showOnFocus: true,
      showRightIcon: false,
      maxDate: function () {
        return new Date();
      },
      size: "default",
      change: function (e) {
        if (e.target) {
          $(e.target).valid();
        }
      },
    });
  }
</script>
@endpush
