import React, { useState, useEffect } from "react";

function DatePicker({ initialDate, onDateChange, disabled = false }) { // Add disabled prop
  const [selectedDate, setSelectedDate] = useState(initialDate);

  useEffect(() => {
    setSelectedDate(initialDate);
  }, [initialDate]);

  const handleDateChange = (event) => {
    const newDate = event.target.value;
    setSelectedDate(newDate);
    if (!disabled) {
      onDateChange(newDate); // Notify parent of the date change only if not disabled
    }
  };

  return (
    <div className="form-group">
      <input
        type="date"
        id="datePicker"
        className="form-control"
        value={selectedDate}
        onChange={handleDateChange}
        placeholder="YYYY-MM-DD"
        disabled={disabled} // Set input to disabled if the prop is true
      />
    </div>
  );
}

export default DatePicker;
