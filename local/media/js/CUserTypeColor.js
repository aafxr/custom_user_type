$(document).ready(function () {
    $('#colorpickerHolder').ColorPicker({
        flat: true,
        color: $('#colorpickerHolderInput').val(),
        onChange: function (hsb, hex, rgb) {
            $('#colorpickerHolderInput').val(hex);
        }
    });
});
