<?php

/**
 * \brief The Cart class manages a user's cart. One of these is stored for each user in a session variable to keep track of all of their selected items before they've purchased them.
 * 
 * V1.05
 */
class Cart{
	/*! A list of cart item models that each store a rate plan to be displayed to the user*/
	public $cart_items; 
	/*! A tally of cart items used to generate a unique cart id for each item added*/
	public $latestItemId; 

	/**
	 * Initializes an empty cart instance.
	 */
	public function __construct(){
		$this->clearCart();
	}

	/**
	 * Clears all items from this cart instance.
	 */
	public function clearCart(){
		$this->cart_items = array();
		$this->latestItemId = 1;		
	}

	/**
	 * Adds a new item to this cart instance. Each item is added with a ProductRatePlanId, a Quantity, and a unique Cart Item identification number
	 * @param $rateplanId The ProductRatePlanId of the item being added
	 * @param $quantity The number of UOM to be applied to this rateplan for all charges with a Per Unit quantity
	 */
	public function addCartItem($rateplanId, $quantity){
		$newCartItem = new Cart_Item($rateplanId, $quantity, $this->latestItemId);
		$newCartItem->ratePlanId = $rateplanId;

		$newCartItem->itemId = $this->latestItemId++;
		
		$newCartItem->quantity = $quantity;
	
		$plan = Catalog::getRatePlan($newCartItem->ratePlanId);
		
		if(isset($plan->Uom)){
			$newCartItem->uom = $plan->Uom;
		} else {
			$newCartItem->uom = null;			
		}
	
		$newCartItem->ratePlanName = $plan!=null ? $plan->Name : 'Invalid Product';
		$newCartItem->ProductName = $plan!=null ? $plan->productName : 'Invalid Product';
		array_push($this->cart_items, $newCartItem);
	}

	/**
	 * Removes an item from the user's cart.
	 * @param $itemId The unique cart item ID of the item being removed from the cart
	 */
	public function removeCartItem($itemId){
		for($i=0;$i<(count($this->cart_items));$i++){
			if($this->cart_items[$i]->itemId==$itemId){
				unset($this->cart_items[$i]);
				$this->cart_items = array_values($this->cart_items);
				return true;
			}
		}
		return false;
	}
}

?>