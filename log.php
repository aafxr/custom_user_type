<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<?php
    echo file_get_contents($_SERVER['DOCUMENT_ROOT'].'/local/dev/test.log');
?>

<script>
    var log = []
    document.querySelectorAll('.section').forEach(s => {
        var title = s.querySelector('.title')
        if (title) title = title.textContent
        else title = 'defaultTitle'
        var result = {title, props: []}
        s.querySelectorAll('.code').forEach(c => {
            try {
                result.props.push(JSON.parse(c.textContent))
            }catch (e){
                console.error(c)
                console.error(e)
            }
        })
        log.push(result)
    })
    window.log = log
</script>
</body>
</html>
