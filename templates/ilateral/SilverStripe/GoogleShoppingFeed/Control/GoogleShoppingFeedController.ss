<?xml version="1.0"?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
	<channel>
		<title>$SiteConfig.Title</title>
		<link>$BaseHref</link>
		<description>$SiteConfig.Tagline</description>
		
        <% loop $Items %> 
			<item>
				<title>$Title</title>
				<g:title>$Title</g:title>
				<description>$Content.Summary</description>
				<g:description>$Content.Summary</g:description>
				<g:id><% if $StockID %>$StockID<% else %>$ID<% end_if %></g:id>
				<link>$AbsoluteLink</link>
				<g:link>$AbsoluteLink</g:link>
				<% if $PrimaryImage.exists %><g:image_link>{$PrimaryImage.AbsoluteLink}</g:image_link><% end_if %>
				<% if $AdditionalImage.exists %><g:additional_image_link>{$AdditionalImage.AbsoluteLink}</g:additional_image_link><% end_if %>
				<g:price>$ShoppingFeedPrice $Top.Currency</g:price>
				<g:condition>$Condition</g:condition>
				<g:availability>$Availability</g:availability>
				<g:brand>$Brand</g:brand>
				<g:identifier_exists><% if $UPIExists %>yes<% else %>no<% end_if %></g:identifier_exists>
				<% if $MPN %><g:mpn>$MPN</g:mpn><% end_if %>
				<% if $GTIN %><g:gtin>$GTIN</g:gtin><% end_if %>
				<% if $GoogleProductCategory.exists %><g:google_product_category>$GoogleProductCategory.GoogleID</g:google_product_category><% end_if %>
				<% loop $Shipping %>
					<g:shipping>
						<% if $Country %><g:country>$Country</g:country><% end_if %>
						<% if $Service %><g:service>$Service</g:service><% end_if %>
						<g:price>$ShoppingFeedPrice $Top.Currency</g:price>
					</g:shipping>
				<% end_loop %>
			</item>
        <% end_loop %>
        
    </channel>
</rss>
