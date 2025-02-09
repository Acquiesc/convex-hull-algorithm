<?php 

$points = generateConvexHullPoints(20); //adjust number of randomly generated points
$date = Date('F d, Y H:i:s'); //current date/time

$hullEdges = findConvexHull($points); //return [0: [$points->index, $points->index2]]

//Extract unique indexes from $hullEdges 2d array
$hullPoints = [];
foreach($hullEdges as $edge) {
    if(!in_array($edge[0], $hullPoints)) {
        array_push($hullPoints, $edge[0]);
    }

    if(!in_array($edge[1], $hullPoints)) {
        array_push($hullPoints, $edge[1]);
    }
}

foreach($hullEdges as $index => $edge) {
    $points[$edge[0]]['connected_to'] = $edge[1];
}

//last two points are not formed with a connection.  Add a duplicate of the second to last point connected to the last point
//so the line visual completes the hull
$secondHullPoint = $hullEdges[count($hullEdges) - 2][0]; // Second to last point in the convex hull
$lastHullPoint = $hullEdges[count($hullEdges) - 1][0]; // Last point in the convex hull
array_push($hullPoints, count($points));
$points[count($points)] = [
    'x' => $points[$secondHullPoint]['x'],
    'y' => $points[$secondHullPoint]['y'],
    'connected_to' => $lastHullPoint,
];

//Display HTML of parsed graph
echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Document</title>

    <style>
        .graph-container {
            width: 75vw;
            height: 80vh;
            margin-left: auto;
            margin-right: auto;
            border: 2px solid black;
            border-radius: 25px;
            position: relative;
            padding: 10px;
        }

        .point {
            position: absolute;
            width: 15px;
            height: 15px;
            color: white;
            text-align: center;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            z-index: 2;
        }

        .hidden-hp {
            z-index: -1 !important;
        }

        .line {
            background-color: blue;
            height: 2px; 
            position: absolute;
        }
    </style>

</head>
<body style='height: 100vh;'>
    <div style='text-align: center;'>
        <p style='margin: 0'>Convex Hull Algorithm</p>
        <p style='margin-top: 0;'><a href='https://aleecoding.dev' target='_blank'>Adam Lee</a></p>
    </div>

    <div class='graph-container'>
        ";
    
    //loop through all points generated on the graph
    foreach($points as $index => $point) {
        //if it's a hull point, highlight red
        if(in_array($index, $hullPoints)) {
            //if it's the additive point for the last remaining convex hull line, z-index it behind the real point
            echo $index == (count($points) - 1) ?
                 "<div class='point hull-point hidden-hp' id='point-{$index}' data-line='{$point['connected_to']}' style='background-color: red; left: {$point['x']}%; bottom: {$point['y']}%'>
                        $index
                    </div>"
                    :
                    //else just highlight point red
                 "<div class='point hull-point' id='point-{$index}' data-line='{$point['connected_to']}' style='background-color: red; left: {$point['x']}%; bottom: {$point['y']}%'>
                            $index
                        </div>";
        } else {
            //else print black point in the middle of the hull
            echo "<div class='point' id='point-{$index}' style='background-color: black; left: {$point['x']}%; bottom: {$point['y']}%'>
                        $index
                    </div>";
        }
    }

echo"</div>

    <script>
        //connect lines between hull points
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.querySelector('.graph-container')
            const hullPoints = document.querySelectorAll('.hull-point')

            const containerRect = container.getBoundingClientRect() //get location of the graph container on the webpage

            for(let i = 0; i < hullPoints.length; i++) {
                let point1 = hullPoints[i] //get first point
                let point2 = document.getElementById('point-' + point1.dataset.line) //get next point
                let point1Rect = point1.getBoundingClientRect() //get location for each points on page
                let point2Rect = point2.getBoundingClientRect() //get location for each points on page

                let x1 = point1Rect.left - containerRect.left  //get coordinates relative to the container //TODO: also account for window.scrollY/window.scrollX
                let y1 = point1Rect.top - containerRect.top  //get coordinates relative to the container //TODO: also account for window.scrollY/window.scrollX
                let x2 = point2Rect.left - containerRect.left  //get coordinates relative to the container //TODO: also account for window.scrollY/window.scrollX
                let y2 = point2Rect.top - containerRect.top  //get coordinates relative to the container //TODO: also account for window.scrollY/window.scrollX
    
                let width = getWidthBetweenPoints(x1, y1, x2, y2)
                let angle = getAngleBetweenPoints(x1, y1, x2, y2)

                let line = document.createElement('div')
                line.classList.add('line')

                line.style.left = x1 + 'px' //align line to the left of the first point
                line.style.top = y1 + 'px' //align line to the left of the first point
                line.style.width = width + 'px' //set line width to distance between points
                line.style.transform = 'rotate(' + angle + 'deg)' //rotate line in direction toward second point
                line.style.transformOrigin = '0% 50%' // transform line around the left rather than center

                container.appendChild(line) //add line to DOM
            }
        });

        function getWidthBetweenPoints(x1, y1, x2, y2) {
            return Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2)) //pythag to find distance between two points
        }

        function getAngleBetweenPoints(x1, y1, x2, y2) {
            return Math.atan2(y2 - y1, x2 - x1) * (180 / Math.PI) //trig to find the angle between first point and line
        }
    </script>

   </body>
</html>";


//generate random set of points
function generateConvexHullPoints(int $num) {
    $points = [];
    
    for($i = 0; $i < $num; $i++) {
        $x = rand(0, 9999) / 100; //random double between 0.00 - 99.99 (converted to %)
        $y = rand(0, 9999) / 100; //random double between 0.00 - 99.99 (converted to %)

        $points[$i] = [
            'x' => $x,
            'y' => $y,
        ];
    }

    usort($points, function($a, $b) {
        return $a['x'] <=> $b['x'];
    });

    return $points;
}

//Convex hull algorithm -- find the smallest polygon enclosing all points in the graph
function findConvexHull($points) {
    if(count($points) < 3) {
        return;
    }

    $hullEdges = [];

    $index = 0;
    for($i = 0; $i < count($points); $i++) {
        for($j = 0; $j < count($points); $j++) {
            if($i == $j) {
                continue; //skip if checking same points
            }

            //echo "Checking $i, $j <br>";
            if(isOnSameSide($points[$i], $points[$j], $points)) {
                $hullEdges[$index] = [$i, $j];
                $index++;
                break;
            }

        }
    }

    return $hullEdges;
}

// > 0: counterclockwise, 
// < 0: points are clockwise, 
// = 0: straight line
function crossProduct($a, $b, $c) {
    return ($b['x'] - $a['x']) * ($c['y'] - $a['y']) 
                - ($b['y'] - $a['y']) * ($c['x'] - $a['x']);
}


//determines whether all points are on the same side of the line between $p1 and $p2
function isOnSameSide($p1, $p2, $points) {
    $side = 0;

    foreach($points as $index => $p) {
        if($p == $p1 || $p == $p2) {
            continue; //skip $p1 and $p2 since they're guaranteed on the line
        }

        $crossProduct = crossProduct($p1, $p2, $p);

        $currentSide = $crossProduct > 0 ? -1 : 1;

        if($side == 0) {
            $side = $currentSide;
        } else if($side != $currentSide) {
            return false;
        }
    }
    
    return true; //all points in the same orientation from the line
}
