import React from "react";
import ValueMetric from "./ValueMetric";
 
const components = {
  'value-metric': ValueMetric
};

export const renderComponent = (component, props, children) => {
    if (typeof components[component] !== "undefined") {
      return React.createElement(components[component], {...props}, children);
    }
}
