import React from "react";
import ValueMetric from "./Domain/ValueMetric";
import PartitionMetric from "./Domain/PartitionMetric";
import TrendMetric from "./Domain/TrendMetric";
import SectionDivider from "./Global/SectionDivider";
import CirclePackMetric from "./Domain/CirclePackMetric";

const components = {
  'value-metric': ValueMetric,
  'partition-metric': PartitionMetric,
  'trend-metric': TrendMetric,
  'circle-pack-metric': CirclePackMetric,
  'section-divider': SectionDivider
};

export const renderComponent = (component, props, children) => {
    if (typeof components[component] !== "undefined") {
      return React.createElement(components[component], {...props}, children);
    }
}
