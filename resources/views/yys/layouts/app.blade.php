<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>@yield('title') - 照妖镜V2</title>
    <link rel="stylesheet" href="/css/sb-admin-2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
    <script>
        window.chartColors = {
            red: 'rgb(255, 99, 132)',
            orange: 'rgb(255, 159, 64)',
            yellow: 'rgb(255, 205, 86)',
            green: 'rgb(75, 192, 192)',
            blue: 'rgb(54, 162, 235)',
            purple: 'rgb(153, 102, 255)',
            grey: 'rgb(201, 203, 207)'
        };
        Chart.defaults.global.maintainAspectRatio = false;
        var helper = {
            gotoDetail: function (inputId) {
                var u = document.getElementById(inputId).value;
                var r = u.match(/\/equip\/\d+\/([\w\-]+)/);
                if( r != null )
                {
                    window.location.href = '/yys/detail/' + r[1];
                }
                else {
                    alert('网址输入有误');
                }
            },
            chartRadarOption: {
                startAngle: 30,
                animation: false,
                legend: {
                    display: false
                },
                scale: {
                    ticks: {
                        beginAtZero: true,
                        maxTicksLimit: 4,
                        display: true,
                        stepSize: 5
                    }
                }
            }
        };
    </script>
    <style>
        .table tr td {
            vertical-align: middle
        }
    </style>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        @include('yys.components.sidebar')

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-light bg-white topbar mb-4 static-top shadow justify-content-between">
                    <div></div>
                    <!-- Topbar Search -->
                    <form onsubmit="helper.gotoDetail('top-search');return false;"
                        class="d-inline-block form-inline ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" id="top-search" class="form-control bg-gray-200 border-0 small"
                                placeholder="输入CBG网址..." />
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    @section('mainContent')
                    @show

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; 2019</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

</body>

</html>