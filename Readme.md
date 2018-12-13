# Sheep Subscription

A Magento 1.x extensions I've worked many years ago and that I didn't complete. This code was **never** used in production
and there is no support offered. It's shared on Github just for educationally reasons.

## Features

- Ability to define subscription types and associate on available products
- Multiple payment methods can be used for renewals (Bank transfer, Cash on delivery, Authorize.net, Paypal Express)
- Renewal engine 
- Promote/demote customers to customer groups based on their active subscriptions
- Ability to customize sales rules with conditions on subscriptions
- Notify by e-mails upcoming renewals or check for possible expired payment methods
- Transactional e-mails related to subscriptions
- Subscription CRUD for customers and admin users
- Adminhtml reports on subscribers, subscription, renewal forecast, stock forecast
- And many other I forgot about

## Un-install SQL

```sql
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