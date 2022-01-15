import React from "react";
import ValueMetric from "./ValueMetric";
import PartitionMetric from "./PartitionMetric";
 
const components = {
  'value-metric': ValueMetric,
  'partition-metric': PartitionMetric,
};

export const renderComponent = (component, props, children) => {
    if (typeof components[component] !== "undefined") {
      return React.createElement(components[component], {...props}, children);
    }
}
