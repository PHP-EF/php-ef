var maxDaysApart = 31;
var today = new Date();
var maxPastDate = new Date(today);
maxPastDate.setDate(today.getDate() - 31);

flatpickr("#assessmentStartAndEndDate", {
  mode: "range",
  minDate: maxPastDate,
  maxDate: today,
  enableTime: true,
  dateFormat: "Y-m-d H:i",
  onChange: function(selectedDates, dateStr, instance) {
    if (selectedDates.length === 1) {
      const startDate = selectedDates[0];
      const maxEndDate = new Date(startDate.getTime() + 31 * 24 * 60 * 60 * 1000); // 31 days later
      const today = new Date();
      instance.set('maxDate', maxEndDate > today ? today : maxEndDate);
    }
    if (selectedDates.length === 2) {
      const startDate = selectedDates[0];
      const endDate = selectedDates[1];
      const diffInDays = (endDate - startDate) / (1000 * 60 * 60 * 24);
      if (diffInDays > 31) {
        toast("Error","","The start and end date cannot exceed 31 days.","warning");
        instance.clear();
      }
    }
  }
});

flatpickr("#reportingStartAndEndDate", {
  mode: "range",
  maxDate: today,
  enableTime: true,
  dateFormat: "Y-m-d H:i"
});