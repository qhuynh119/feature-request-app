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
            // populate feature priority based on the client in feature request form
            $.ajax({
                url: '<?= $_SERVER['PHP_SELF'] ?>',
                data: 'action=get_client_priority&client_id=' + $('#feature_client').val(),
                success: function(data) {
                    $('#feature_priority').html(data);
                }
            });
        }

        function set_table_data() {
            // populate requested features table based on specific client
            console.log($('#client_filter').val());
            $.ajax({
                url: '<?= $_SERVER['PHP_SELF'] ?>',
                data: 'action=get_table_data&client_id=' + $('#client_filter').val(),
                success: function (data) {
                    console.log(data);
                    $('#feature_table tbody').html(data);
                }
            });
        }

        $(document).ready(function() {

        });
    </script>
</body>
</html>
