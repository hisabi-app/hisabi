import React from "react";
import ValueMetric from "./Domain/ValueMetric";
import PartitionMetric from "./Domain/PartitionMetric";
import TrendMetric from "./Domain/TrendMetric";
 
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
