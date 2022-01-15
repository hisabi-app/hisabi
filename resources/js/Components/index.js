import React from "react";
import ValueMetric from "./ValueMetric";
import PartitionMetric from "./PartitionMetric";
import TrendMetric from "./TrendMetric";
 
const components = {
  'value-metric': ValueMetric,
  'partition-metric': PartitionMetric,
  'trend-metric': TrendMetric,
};

export const renderComponent = (component, props, children) => {
    if (typeof components[component] !== "undefined") {
      return React.createElement(components[component], {...props}, children);
    }
}
