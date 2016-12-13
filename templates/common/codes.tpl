{_v($item)}

{if $item.codes}
	{foreach $item.codes as $code}
		{_v($code)}
	{/foreach}
{/if}