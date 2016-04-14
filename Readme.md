# Implemented Authorize.NET

# Unininstall SQL

```
-- Drop subscription related tables
DROP TABLE ss_subscription;
DROP TABLE ss_product_subscription_type;
DROP TABLE ss_type;

-- Remove product eav_attributes
DELETE FROM eav_attribute where code in ('pss_is_subscription', 'pss_subscription_type');

-- Remove sales columns
ALTER TABLE sales_flat_quote DROP COLUMN pss_is_subscription;
ALTER TABLE sales_flat_quote DROP COLUMN pss_has_subscriptions;


--  Remove install script
DELETE FROM core_resource where code = 'sheep_subscription_setup';
```