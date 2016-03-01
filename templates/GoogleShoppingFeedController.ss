<?xml version="1.0"?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
	<channel>
		<title>$SiteConfig.Title</title>
		<link>$BaseHref</link>
		<description>$SiteConfig.Tagline</description>

        <% loop $Items %>
		<item>
			<g:id><% if $StockID %>$StockID<% else %>$ID<% end_if %></g:id>
			<g:title>$Title</g:title>
			<g:description>$Content.Summary</g:description>
			<g:link>$AbsoluteLink</g:link>
			<% if $Images %><g:image_link>{$BaseHref}{$Images.First.Filename}</g:image_link>
			<% else %><g:image_link>{$BaseHref}{$Image.Filename}</g:image_link><% end_if %>
			<g:price>$getPriceAndTax(2) $Top.Currency</g:price>
            <g:condition>$Condition</g:condition>
			<g:availability>$Availability</g:availability>
			<g:brand>$Brand</g:brand>
			<g:mpn>$MPN</g:mpn>

            <% loop $Shipping %>
			<g:shipping>
				<g:country>$Country</g:country>
				<g:service>$Title</g:service>
				<g:price>$Total(2) $Top.Currency</g:price>
			</g:shipping>
            <% end_loop %>
		</item>
        <% end_loop %>

    </channel>
</rss>
