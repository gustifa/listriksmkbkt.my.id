$(function() {
    "use strict";

    // Fungsi untuk membuat gradient (agar kode lebih rapi)
    function createGradient(ctx, startColor, endColor) {
        var gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, startColor);
        gradient.addColorStop(1, endColor);
        return gradient;
    }
	
    // ===================================
    // chart 1 (Bar Chart)
    // ===================================
    var chart1_el = document.getElementById("chart1");
    if (chart1_el) { // <--- PENGECEKAN ELEMEN PENTING
        var ctx1 = chart1_el.getContext('2d');
        
        var gradientStroke1 = createGradient(ctx1, '#6078ea', '#17c5ea'); 
        var gradientStroke2 = createGradient(ctx1, '#ff8359', '#ffdf40');

        var myChart1 = new Chart(ctx1, {
            type: 'bar',
            data: {
              labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
              datasets: [{
                label: 'Laptops',
                data: [65, 59, 80, 81,65, 59, 80, 81,59, 80, 81,65],
                borderColor: gradientStroke1,
                backgroundColor: gradientStroke1,
                hoverBackgroundColor: gradientStroke1,
                pointRadius: 0,
                fill: false,
                borderRadius: 20,
                borderWidth: 0
              }, {
                label: 'Mobiles',
                data: [28, 48, 40, 19,28, 48, 40, 19,40, 19,28, 48],
                borderColor: gradientStroke2,
                backgroundColor: gradientStroke2,
                hoverBackgroundColor: gradientStroke2,
                pointRadius: 0,
                fill: false,
                borderRadius: 20,
                borderWidth: 0
              }]
            },
            
            options: {
                      maintainAspectRatio: false,
                      barPercentage: 0.5,
                      categoryPercentage: 0.8,
                      plugins: {
                          legend: {
                              display: false,
                          }
                      },
                      scales: {
                          y: {
                              beginAtZero: true
                          }
                      }
                  }
        });
    } // <--- Akhir if chart1_el
	  
	 
    // ===================================
    // chart 2 (Doughnut Chart)
    // ===================================
    var chart2_el = document.getElementById("chart2");
    if (chart2_el) { // <--- PENGECEKAN ELEMEN PENTING
        var ctx2 = chart2_el.getContext('2d');

        var gradientStroke3 = createGradient(ctx2, '#fc4a1a', '#f7b733');
        var gradientStroke4 = createGradient(ctx2, '#4776e6', '#8e54e9');
        var gradientStroke5 = createGradient(ctx2, '#ee0979', '#ff6a00');
        var gradientStroke6 = createGradient(ctx2, '#42e695', '#3bb2b8');

        var myChart2 = new Chart(ctx2, {
            type: 'doughnut',
            data: {
              labels: ["Jeans", "T-Shirts", "Shoes", "Lingerie"],
              datasets: [{
                backgroundColor: [
                  gradientStroke3,
                  gradientStroke4,
                  gradientStroke5,
                  gradientStroke6
                ],
                hoverBackgroundColor: [
                  gradientStroke3,
                  gradientStroke4,
                  gradientStroke5,
                  gradientStroke6
                ],
                data: [25, 80, 25, 25],
                borderWidth: [1, 1, 1, 1]
              }]
            },
            options: {
              maintainAspectRatio: false,
              cutout: 82,
              plugins: {
                legend: {
                    display: false,
                 }
              }
           }
        });
    } // <--- Akhir if chart2_el

   
    // ===================================
    // worl map (jVectorMap)
    // ===================================
    var map_el = jQuery('#geographic-map-2');
    if (map_el.length) { // <--- PENGECEKAN ELEMEN PENTING (jQuery check)
        map_el.vectorMap(
        {
            map: 'world_mill_en',
            backgroundColor: 'transparent',
            borderColor: '#818181',
            borderOpacity: 0.25,
            borderWidth: 1,
            zoomOnScroll: false,
            color: '#009efb',
            regionStyle : {
                initial : {
                  fill : '#008cff'
                }
              },
            markerStyle: {
              initial: {
                    r: 9,
                    'fill': '#fff',
                    'fill-opacity':1,
                    'stroke': '#000',
                    'stroke-width' : 5,
                    'stroke-opacity': 0.4
                    },
                    },
            enableZoom: true,
            hoverColor: '#009efb',
            markers : [{
                latLng : [21.00, 78.00],
                name : 'Lorem Ipsum Dollar'
              
              }],
            hoverOpacity: null,
            normalizeFunction: 'linear',
            scaleColors: ['#b6d6ff', '#005ace'],
            selectedColor: '#c9dfaf',
            selectedRegions: [],
            showTooltip: true,
        });
    } // <--- Akhir if map_el


    // ===================================
    // chart 3 (Line Chart)
    // ===================================
    var chart3_el = document.getElementById('chart3');
    if (chart3_el) { // <--- PENGECEKAN ELEMEN PENTING
        var ctx3 = chart3_el.getContext('2d');

        var gradientStroke7 = createGradient(ctx3, '#00b09b', '#96c93d');

        var myChart3 = new Chart(ctx3, {
            type: 'line',
            data: {
              labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
              datasets: [{
                    label: 'Facebook',
                    data: [5, 30, 16, 23, 8, 14, 2],
                    backgroundColor: [
                      gradientStroke7
                    ],
                    fill: {
                        target: 'origin',
                        above: 'rgb(21 202 32 / 15%)',   
                    }, 
                    tension: 0.4,
                    borderColor: [
                      gradientStroke7
                    ],
                    borderWidth: 3
                }]
            },
            options: {
                      maintainAspectRatio: false,
                      plugins: {
                          legend: {
                              display: false,
                          }
                      },
                      scales: {
                          y: {
                              beginAtZero: true
                          }
                      }
                  }
        });
    } // <--- Akhir if chart3_el


    // ===================================
    // chart 4 (Pie Chart)
    // ===================================
    var chart4_el = document.getElementById("chart4");
    if (chart4_el) { // <--- PENGECEKAN ELEMEN PENTING
        var ctx4 = chart4_el.getContext('2d');

        var gradientStroke8 = createGradient(ctx4, '#ee0979', '#ff6a00');
        var gradientStroke9 = createGradient(ctx4, '#283c86', '#39bd3c');
        var gradientStroke10 = createGradient(ctx4, '#7f00ff', '#e100ff');

        var myChart4 = new Chart(ctx4, {
            type: 'pie',
            data: {
              labels: ["Completed", "Pending", "Process"],
              datasets: [{
                backgroundColor: [
                  gradientStroke8,
                  gradientStroke9,
                  gradientStroke10
                ],
                 hoverBackgroundColor: [
                  gradientStroke8,
                  gradientStroke9,
                  gradientStroke10
                ],
                data: [50, 50, 50],
          borderWidth: [1, 1, 1]
              }]
            },
            options: {
              maintainAspectRatio: false,
              cutout: 95,
              plugins: {
                legend: {
                    display: false,
                 }
              }
              
           }
        });
    } // <--- Akhir if chart4_el


    // ===================================
    // chart 5 (Bar Chart)
    // ===================================
    var chart5_el = document.getElementById("chart5");
    if (chart5_el) { // <--- PENGECEKAN ELEMEN PENTING
        var ctx5 = chart5_el.getContext('2d');
       
        var gradientStroke11 = createGradient(ctx5, '#f54ea2', '#ff7676');
        var gradientStroke12 = createGradient(ctx5, '#42e695', '#3bb2b8');

        var myChart5 = new Chart(ctx5, {
            type: 'bar',
            data: {
              labels: [1, 2, 3, 4, 5],
              datasets: [{
                label: 'Clothing',
                data: [40, 30, 60, 35, 60],
                borderColor: gradientStroke11,
                backgroundColor: gradientStroke11,
                hoverBackgroundColor: gradientStroke11,
                pointRadius: 0,
                fill: false,
                borderWidth: 1
              }, {
                label: 'Electronic',
                data: [50, 60, 40, 70, 35],
                borderColor: gradientStroke12,
                backgroundColor: gradientStroke12,
                hoverBackgroundColor: gradientStroke12,
                pointRadius: 0,
                fill: false,
                borderWidth: 1
              }]
            },
            options: {
                      maintainAspectRatio: false,
                      barPercentage: 0.5,
                      categoryPercentage: 0.8,
                      plugins: {
                          legend: {
                              display: false,
                          }
                      },
                      scales: {
                          y: {
                              beginAtZero: true
                          }
                      }
                  }
        });
    } // <--- Akhir if chart5_el

});