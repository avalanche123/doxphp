.. php:namespace:: Imagine\Draw

.. php:interface:: DrawerInterface


   .. php:method:: arc

      Draws an arc on a starting at a given x, y coordinates under a given
start and end angles

      :param Imagine\Image\PointInterface $center:
      :param Imagine\Image\BoxInterface $size:
      :param integer $start:
      :param integer $end:
      :param Imagine\Image\Color $color:

      :throws Imagine\Exception\RuntimeException:

      :returns Imagine\Draw\DrawerInterface:

   .. php:method:: chord

      Same as arc, but also connects end points with a straight line

      :param Imagine\Image\PointInterface $center:
      :param Imagine\Image\BoxInterface $size:
      :param integer $start:
      :param integer $end:
      :param Imagine\Image\Color $color:
      :param Boolean $fill:

      :throws Imagine\Exception\RuntimeException:

      :returns Imagine\Draw\DrawerInterface:

   .. php:method:: ellipse

      Draws and ellipse with center at the given x, y coordinates, and given
width and height

      :param Imagine\Image\PointInterface $center:
      :param Imagine\Image\BoxInterface $size:
      :param Imagine\Image\Color $color:
      :param Boolean $fill:

      :throws Imagine\Exception\RuntimeException:

      :returns Imagine\Draw\DrawerInterface:

   .. php:method:: line

      Draws a line from start(x, y) to end(x, y) coordinates

      :param Imagine\Image\PointInterface $start:
      :param Imagine\Image\PointInterface $end:
      :param Imagine\Image\Color $outline:

      :returns Imagine\Draw\DrawerInterface:

   .. php:method:: pieSlice

      Same as arc, but connects end points and the center

      :param Imagine\Image\PointInterface $center:
      :param Imagine\Image\BoxInterface $size:
      :param integer $start:
      :param integer $end:
      :param Imagine\Image\Color $color:
      :param Boolean $fill:

      :throws Imagine\Exception\RuntimeException:

      :returns Imagine\Draw\DrawerInterface:

   .. php:method:: dot

      Places a one pixel point at specific coordinates and fills it with
specified color

      :param Imagine\Image\PointInterface $position:
      :param Imagine\Image\Color $color:

      :throws Imagine\Exception\RuntimeException:

      :returns Imagine\Draw\DrawerInterface:

   .. php:method:: polygon

      Draws a polygon using array of x, y coordinates. Must contain at least
three coordinates

      :param array $coordinates:
      :param Imagine\Image\Color $color:
      :param Boolean $fill:

      :throws Imagine\Exception\RuntimeException:

      :returns Imagine\Draw\DrawerInterface:

   .. php:method:: text

      Annotates image with specified text at a given position starting on the
top left of the final text box

The rotation is done CW

      :param string $string:
      :param Imagine\Image\AbstractFont $font:
      :param Imagine\Image\PointInterface $position:
      :param integer $angle:

      :throws Imagine\Exception\RuntimeException:

      :returns Imagine\Draw\DrawerInterface: