<?php
//<namespace
namespace cps;
//namespace>
/**
 * The CPS_ListFacetsRequest class is a wrapper for the Response class for the list-facets command
 * @package CPS
 * @see CPS_ListFacetsResponse
 */
class CPS
{

  /**
   * Escapes <, > and & characters in the given term for inclusion into XML (like the search query). Also wraps the term in XML tags if xpath is specified.
   * Note that this function doesn't escape the @, $, " and other symbols that are meaningful in a search query. If You want to escape input that comes directly
   * from the user and that isn't supposed to contain any search operators at all, it's probably better to use {@link CPS_QueryTerm}
   * @param string $term the term to be escaped (e.g. a search query term)
   * @param string $xpath an optional xpath, to be specified if the search term is to be searched under a specific xpath
   * @param bool $escape an optional parameter - whether to escape the term's XML
   * @see CPS::QueryTerm
   */
  public static function Term($term, $xpath = '', $escape = TRUE)
  {
    $prefix = ' ';
    $postfix = ' ';
    if (strlen($xpath) > 0) {
      $tags = explode('/', $xpath);
      foreach ($tags as $tag) {
        if (strlen($tag) > 0) {
          $prefix .= '<' . $tag . '>';
          $postfix = '</' . $tag . '>' . $postfix;
        }
      }
    }

    // ENT_NOQUOTES	Will leave both double and single quotes unconverted.
    if (defined('ENT_SUBSTITUTE')) { // php >5.4
      // ENT_SUBSTITUTE	Replace invalid code unit sequences with a Unicode Replacement Character U+FFFD (UTF-8) or &#FFFD; (otherwise) instead of returning an empty string.
      return $prefix . ($escape ? htmlspecialchars($term, ENT_NOQUOTES | ENT_SUBSTITUTE) : $term) . $postfix;
    }
    return $prefix . ($escape ? htmlspecialchars($term, ENT_NOQUOTES) : $term) . $postfix;
  }

  /**
   * Escapes <, > and & characters, as well as @"{}()=$~+ (search query operators) in the given term for inclusion into the search query.
   * Also wraps the term in XML tags if xpath is specified.
   * @param string $term the term to be escaped (e.g. a search query term)
   * @param string $xpath an optional xpath, to be specified if the search term is to be searched under a specific xpath
   * @param string $allowed_symbols a string containing operator symbols that the user is allowed to use (e.g. ")
   * @see CPS::Term
   */
  public static function QueryTerm($term, $xpath = '', $allowed_symbols = '')
  {
    $newTerm = '';
    $len = strlen($term);
    for ($x = 0; $x < $len; ++$x) {
      switch ($term[$x]) {
        case '@':
        case '$':
        case '"':
        case '=':
        case '>':
        case '<':
        case ')':
        case '(':
        case '{':
        case '}':
        case '~':
        case '+':
          if (strstr($allowed_symbols, $term[$x]) === FALSE)
            $newTerm .= '\\';
        default:
          $newTerm .= $term[$x];
      }
    }
    return self::Term($newTerm, $xpath);
  }

  /**
   * Converts a given query array to a query string
   * @param array $array the query array
   * @return string
   */
  public static function QueryArray($array)
  {
    $r = '';
    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $r .= self::Term(self::QueryArray($value), $key, false);
      } else {
        $r .= self::Term($value, $key);
      }
    }
    return $r;
  }

  /**
   * Returns an circle definition string with provided center and radius
   * @function
   * @param String $name name of a shape, should be a valid xml name
   * @param array $center array with two elements identifying center of circle
   * @param double|String $radius radius of circle with optional distance type (km/mi), default is km
   * @param String $tagName1 tag name of first coordinate (e.g. latitude), if not passed, then default configuration values will be used
   * @param String $tagName2 tag name of second coordinate (e.g. longitude), if not passed, then default configuration values will be used
   * @param String $coord_type coordinate type, either latlong or plane
   */
  public static function CircleDefinition($name, $center, $radius, $tagName1 = null, $tagName2 = null, $coord_type = null)
  {
    $res = '<' . $name . '>';
    $res .= '<center>' . $center[0] . ' ' . $center[1] . '</center>';
    $res .= '<radius>' . $radius . '</radius>';
    if ($tagName1)
      $res .= '<coord1_tag_name>' . $tagName1 . '</coord1_tag_name>';
    if ($tagName2)
      $res .= '<coord2_tag_name>' . $tagName2 . '</coord2_tag_name>';
    if ($coord_type)
      $res .= '<coord_type>' . $coord_type . '</coord_type>';
    $res .= '</' . $name . '>';
    return $res;
  }

  /**
   * Returns an polygon definition string from provided vertice points
   * @function
   * @param String $name name of a shape, should be a valid xml name
   * @param array $vertices array of vertice coordinates identifying polygon each element should contain array of two elements which correspond to vertice coordinates
   * @param String $tagName1 tag name of first coordinate (e.g. latitude), if not passed, then default configuration values will be used
   * @param String $tagName2 tag name of second coordinate (e.g. longitude), if not passed, then default configuration values will be used
   * @param String $coord_type coordinate type, either latlong or plane
   */
  public static function PolygonDefinition($name, $vertices, $tagName1 = null, $tagName2 = null, $coord_type = null)
  {
    $res = '<' . $name . '>';
    foreach ($vertices as $vertice) {
      $res .= $vertice[0] . ' ' . $vertice[1] . '; ';
    }
    if ($tagName1)
      $res .= '<coord1_tag_name>' . $tagName1 . '</coord1_tag_name>';
    if ($tagName2)
      $res .= '<coord2_tag_name>' . $tagName2 . '</coord2_tag_name>';
    if ($coord_type)
      $res .= '<coord_type>' . $coord_type . '</coord_type>';
    $res .= '</' . $name . '>';
    return $res;
  }

  /**
   * Returns an ordering string for sorting by relevance
   * @see CPS_SearchRequest::setOrdering()
   * @param string $ascdesc optional parameter to specify ascending/descending order. By default most relevant documents are returned first
   */
  public static function RelevanceOrdering($ascdesc = '')
  {
    return '<relevance>' . htmlspecialchars($ascdesc, ENT_NOQUOTES) . '</relevance>';
  }

  /**
   * Returns an ordering string for sorting by a numeric field
   * @see CPS_SearchRequest::setOrdering()
   * @param string $tag the xpath of the tag by which You wish to perform sorting
   * @param string $ascdesc optional parameter to specify ascending/descending order. By default ascending order is used.
   */
  public static function NumericOrdering($tag, $ascdesc = 'ascending')
  {
    return '<numeric>' . self::Term($ascdesc, $tag) . '</numeric>';
  }

  /**
   * Returns an ordering string for sorting by a date field
   * @see CPS_SearchRequest::setOrdering()
   * @param string $tag the xpath of the tag by which You wish to perform sorting
   * @param string $ascdesc optional parameter to specify ascending/descending order. By default ascending order is used.
   */
  public static function DateOrdering($tag, $ascdesc = 'ascending')
  {
    return '<date>' . self::Term($ascdesc, $tag) . '</date>';
  }

  /**
   * Returns an ordering string for sorting by a string field
   * @see CPS_SearchRequest::setOrdering()
   * @param string $tag the xpath of the tag by which You wish to perform sorting
   * @param string $lang specifies the language (collation) to be used for ordering. E.g. "en"
   * @param string $ascdesc optional parameter to specify ascending/descending order. By default ascending order is used.
   */
  public static function StringOrdering($tag, $lang, $ascdesc = 'ascending')
  {
    return '<string>' . self::Term($ascdesc . ',' . $lang, $tag) . '</string>';
  }

  /**
   *
   */
  public static function GenericDistanceOrdering($type, $array, $ascdesc)
  {
    $res = '<distance type="' . htmlspecialchars($type) . '" order="' . htmlspecialchars($ascdesc) . '">';
    foreach ($array as $path => $value) {
      $res .= self::Term($value, $path);
    }
    $res .= '</distance>';
    return $res;
  }

  /**
   * Returns an ordering string for sorting by distance from a latitude/longitude coordinate pair
   * @see CPS_SearchRequest::setOrdering()
   * @param array $array an associative array with tag xpaths as keys and centerpoint coordinates as values. Should contain exactly two elements - latitude first and longitude second.
   * @param string $ascdesc optional parameter to specify ascending/descending order. By default ascending order is used.
   */
  public static function LatLonDistanceOrdering($array, $ascdesc = 'ascending')
  {
    return self::GenericDistanceOrdering('latlong', $array, $ascdesc);
  }

  /**
   * Returns an ordering string for sorting by distance from specified coordinates on a geometric plane
   * @see CPS_SearchRequest::setOrdering()
   * @param array $array an associative array with tag xpaths as keys and centerpoint coordinates as values.
   * @param string $ascdesc optional parameter to specify ascending/descending order. By default ascending order is used.
   */
  public static function PlaneDistanceOrdering($array, $ascdesc = 'ascending')
  {
    return self::GenericDistanceOrdering('plane', $array, $ascdesc);
  }
}
