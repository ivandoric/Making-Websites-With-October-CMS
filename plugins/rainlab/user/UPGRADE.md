# Upgrade guide

- [Upgrading to 1.1 from 1.0](#upgrade-1.1)

<a name="upgrade-1.1"></a>
## Upgrading To 1.1

The User plugin has been split apart in to smaller more manageable plugins. These fields are no longer provided by the User plugin: `company`, `phone`, `street_addr`, `city`, `zip`, `country`, `state`. This is a non-destructive upgrade so the columns will remain in the database untouched.

Country and State models have been removed and can be replaced by installing the plugin **RainLab.Location**. The remaining profiles fields can be replaced by installing the plugin **RainLab.UserPlus**.

In short, to retain the old functionaliy simply install the following plugins:

- RainLab.Location
- RainLab.UserPlus
