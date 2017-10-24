# Contao Subcolumns Bootstrap Bundle

This Contao 4 bundle adds support for the bootstrap 4 grid to [felixpfeiffer/subcolumns](https://github.com/felixpfeiffer/Contao-Subcolumns).

![alt config](docs/config.png)

Courtesy to:

- [contao-legacy/subcolumns_bootstrap_customize](https://legacy-packages-via.contao-community-alliance.org/packages/contao-legacy/subcolumns_bootstrap_customize)

## Technical instructions

1. Specify "Bootstrap 4" in the subcolumns configuration
2. Navigate to the subcolumns section on the left and create a column set.
3. Navigate to the desired article and add a new Column set start element (the other elements are created automatically).
4. Choose the column set you created in step 2.

## Known issues

- sorting via sort anchor (ajax) isn't working -> use synchronous cut action instead (the blue arrow)