<?php
/**
 * \brief The Catalog class manages and caches all Product data retrieved from the configured Zuora tenant
 *
 * V1.05
 */
class Catalog{
	private static $lastSync;

	/**
	 * Reads the Product Catalog Data from Zuora and saves it to a JSON cache stored on the server to reduce load times. This method must be called each time the Product Catalog is changed in Zuora to ensure the catalog is not out of date for the user.
	 * @return A model containing all necessary information needed to display the products and rate plans in the product catalog
	 */
	public static function refreshCache(){
		//Initialize Zuora API Instance
		include('./config.php');
		$zapi = new zApi();

		//For each classification
		$fieldGroups;
		$numGroups;
		if($showAllProducts){
			$numGroups = 1;
			$fieldGroups = array('');
		} else {
			$numGroups = count($groupingFieldValues);
			$fieldGroups = $groupingFieldValues;
		}

		$catalog_groups = array();
		foreach($fieldGroups as $fieldGroup){
			$catalog_group = new Catalog_Group();
			$catalog_group->Name = $fieldGroup;
			$catalog_group->products = array();

			date_default_timezone_set('America/Los_Angeles');
			$curDate = date('Y-m-d\TH:i:s',time());
			
			//Get All Products
			$productZoql = "select Id,Name,SKU,Description from Product where EffectiveStartDate<'".$curDate."' and EffectiveEndDate>'".$curDate."'";
			if(!$showAllProducts){
				$productZoql .= " and ".$groupingField."='".$fieldGroup."'";
			}
			$result = $zapi->zQuery($productZoql);	
			$qProducts = array();
			if($result->result!=null) {
				$qProducts = $result->result->records;
			} else {
				addErrors(null,'No Products found.');
				return;
			}
			
			//Set up Catalog_Product objects
			foreach($qProducts as $p){
				$catalog_product = new Catalog_Product();
				$catalog_product->Id = $p->Id;
				$catalog_product->Name = $p->Name;
				$catalog_product->Description = isset($p->Description) ? $p->Description : "";
		
				//Get RatePlans for this Product
				$result = $zapi->zQuery("select Id,Name,Description from ProductRatePlan where ProductId='".$catalog_product->Id."' and EffectiveStartDate<'".$curDate."' and EffectiveEndDate>'".$curDate."' ");
				$qRatePlans = array();
				$catalog_product->ratePlans = array();
				if($result->result!=null) {
					$qRatePlans = $result->result->records;
					if($qRatePlans!=NULL){
						foreach($qRatePlans as $rp){
							$catalog_rateplan = new Catalog_RatePlan();
							$catalog_rateplan->Id = $rp->Id;
							$catalog_rateplan->Name = $rp->Name;
							$catalog_rateplan->productName = $p->Name;
							$catalog_rateplan->Description = isset($rp->Description) ? $rp->Description : "";
		
							//Get Charges for the Rate Plan
							$result = $zapi->zQuery("select Id,Name,Description,UOM,ChargeModel,ChargeType from ProductRatePlanCharge where ProductRatePlanId='".$catalog_rateplan->Id."'");
							$qCharges = array();
							$quantifiable = false;
							$planUom = '';
							$catalog_rateplan->charges = array();
							if($result->result!=null) {
								$qCharges = $result->result->records;
								if($qCharges!=NULL){
									foreach($qCharges as $rpc){
										$catalog_charge = new Catalog_Charge();
										$catalog_charge->Id = $rpc->Id;
										$catalog_charge->Name = $rpc->Name;
										$catalog_charge->Description = isset($rpc->Description) ? $rpc->Description : "";
										$catalog_charge->ChargeModel = $rpc->ChargeModel ;
										$catalog_charge->ChargeType = $rpc->ChargeType ;
										
										if(($catalog_charge->ChargeType!='Usage') && ($catalog_charge->ChargeModel=='Per Unit Pricing' || $catalog_charge->ChargeModel=='Tiered Pricing' || $catalog_charge->ChargeModel=='Volume Pricing')){
											$catalog_charge->Uom = $rpc->UOM;
											$planUom = $rpc->UOM;
											$quantifiable = true;
										}
										
										array_push($catalog_rateplan->charges, $catalog_charge);
									}
								}
							}
							$catalog_rateplan->Uom = $planUom;
							$catalog_rateplan->quantifiable = $quantifiable;
		
							array_push($catalog_product->ratePlans, $catalog_rateplan);
						}
						array_push($catalog_group->products, $catalog_product);
					}
				}
			}
			array_push($catalog_groups, $catalog_group);
		}
		$catalogJson = json_encode($catalog_groups);

		$lastSync = date(NULL);
		
		//Cache product list
		$myFile = $cachePath;
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $catalogJson);
		fclose($fh);

		return $catalog_groups;
	}

	/**
	 * Reads the Product Catalog Data from the locally saved JSON cache. If no cache exists, this will refresh the catalog from Zuora first.
	 * @return A model containing all necessary information needed to display the products and rate plans in the product catalog
	 */
	public static function readCache(){
		require('./config.php');
		if(!file_exists($cachePath)){
			return self::refreshCache();
		}
		$myFile = $cachePath;
		$fh = fopen($myFile, 'r');
		$catalogJson = fread($fh, filesize($myFile));
		fclose($fh);
		$catalog_groups = json_decode($catalogJson);
		return $catalog_groups;
	}
	
	/**
	 * Given a RatePlan ID, retrieves all rateplan information by searching through the cached catalog file
	 * @return RatePlan model
	 */
	public static function getRatePlan($rpId){
		$catalog_groups = self::readCache();
		foreach($catalog_groups as $group){
			foreach($group->products as $product){
				foreach($product->ratePlans as $ratePlan){
					if($ratePlan->Id == $rpId){
						return $ratePlan;
					}
				}
			}
		}
		return NULL;
	}
}

?>