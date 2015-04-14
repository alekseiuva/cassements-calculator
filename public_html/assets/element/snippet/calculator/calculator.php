<?php
/**
 * calculator.php
 * @version 0.1 (2015-04-11)
 *
 * @desc A snippet for calculator data.
 * @note 
 *
 * @param $id {integer} - The resource id. Current is default
 *
 * @link 
 * 
 * @required 
 * - Snippet getInheritField
 * - Snippet ddGetMultipleField
 */

$id = (int)$id?(int)$id:$modx->documentObject['id']; 


function calcRecursive($id,$debug=false) {
 global $modx;
 $doc = $modx->getTemplateVarOutput(explode(",","alias,pagetitle,image,photos,calculator,isfolder"),$id);
 $outAr = array();
 $pidAr = $modx->getParentIds($id);
 $deep = count($pidAr);
 $childs = array();
 $outNames = array(
  "alias"=>  $doc['alias'],
  "name"=>  $doc['pagetitle'],
 );
 if ($debug) $outNames["debug"] = "ID:$id, Deep:$deep";
 if ($doc['longtitle']) $outNames["title"] = $doc['longtitle'];
 foreach ($modx->getChildIds($id,1) as $alias => $chId) 
  $childs[$chId] = calcRecursive($chId,$debug);
 
 if ($deep == 0) {
  // Корень. различные общие настройки
  $outAr = array_merge($outNames, array(
   "reference" => $modx->runSnippet("ddGetMultipleField",array( "docId" => $id, "docField" => 'calculator', "outputFormat" => 'array')),
   "data" => array_values($childs)
  ));
 } else if ($deep == 1) {
  // Первый уровень - группы (вкладки в пользовательском интерфейсе)
  $outAr = array_merge($outNames, array());
  $getCols = $modx->runSnippet("ddGetMultipleField",array( "docId" => $id, "docField" => 'calculator', "outputFormat" => 'array')); 
  if ($getCols && is_array($getCols)) $outAr["cols"] = $getCols; 
  if ($childs) $outAr["data"] = $childs;
 } else if ($doc['isfolder']) {
  // Группировка по подкатегориям
  $outAr = $outNames;
  $outAr["group"] =  $childs;
 } else {
  // конечные объекты с данными
  $outAr["data_type"] = $modx->runSnippet('getInheritField',array('id'=>$id, 'field'=>'calculator_type'));
  $outAr = array_merge($outNames, $outAr, array(
   "preview" => $doc['image'],
   "image" => $doc['photos'],
   "data" => ($outAr["data_type"]=="multiple" ?
    $doc['calculator']:
    $modx->runSnippet("ddGetMultipleField",array("docId" => $id, "docField" => 'calculator', "outputFormat" => 'array')))
  ));
 }
 return $outAr;
}
$rAr = calcRecursive($id,1);
$result = json_encode($rAr,true);
return $result;
