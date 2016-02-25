<?php
/**************************************************
 *** FOOTER
 **************************************************/
?>
    <!-- jQuery 1.12.1 -->
    <script src="https://code.jquery.com/jquery-1.12.1.min.js" type="text/javascript"></script>
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.min.js" type="text/javascript"></script>

    <script>
        function set_client_priority() {
            $.ajax({
                url: '<?= $_SERVER['PHP_SELF'] ?>',
                data: 'action=get_client_priority&client_id=' + $('#feature_client').val(),
                success: function(data) {
                    $('#feature_priority').html(data);
                }
            });
        }

        $(document).ready(function() {

        });
    </script>
</body>
</html>
